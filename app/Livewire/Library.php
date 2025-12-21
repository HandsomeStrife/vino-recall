<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardRepository;
use Domain\Category\Repositories\CategoryRepository;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Actions\UnenrollUserFromDeckAction;
use Domain\Deck\Data\DeckData;
use Domain\Deck\Helpers\DeckImageHelper;
use Domain\Deck\Repositories\DeckRepository;
use Domain\Material\Repositories\MaterialRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Library extends Component
{
    public ?int $categoryId = null;

    public string $activeTab = 'enrolled';

    public ?int $scrollToDeckId = null;

    public function mount(?string $identifier = null): void
    {
        if ($identifier) {
            $this->activeTab = 'browse';
            // Find the deck by identifier to get its ID for scrolling
            $deck_repository = app(DeckRepository::class);
            $deck = $deck_repository->findByIdentifier($identifier);
            if ($deck) {
                $this->scrollToDeckId = $deck->id;
            }
        }
    }

    public function filterByCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function enrollInDeck(int $deckId, EnrollUserInDeckAction $enrollAction, UserRepository $userRepository): void
    {
        $user = $userRepository->getLoggedInUser();
        $enrollAction->execute($user->id, $deckId);

        $this->dispatch('deck-enrolled');
    }

    public function unenrollFromDeck(int $deckId, UnenrollUserFromDeckAction $unenrollAction, UserRepository $userRepository): void
    {
        $user = $userRepository->getLoggedInUser();
        $unenrollAction->execute($user->id, $deckId);

        $this->dispatch('deck-unenrolled');
    }

    public function render(
        DeckRepository $deck_repository,
        CardRepository $card_repository,
        UserRepository $user_repository,
        CategoryRepository $category_repository,
        MaterialRepository $material_repository
    ) {
        $user = $user_repository->getLoggedInUser();

        // Get all categories for the filter
        $categories = $category_repository->getAll();

        // Get enrolled decks and available decks
        $enrolled_decks = $deck_repository->getUserEnrolledDecks($user->id);
        $available_decks = $deck_repository->getAvailableDecks($user->id);

        // Get individually enrolled child decks (enrolled in child but not parent)
        $individual_child_decks = $deck_repository->getIndividuallyEnrolledChildDecks($user->id);

        // Build stats for enrolled decks
        $enrolled_with_stats = $enrolled_decks->map(function (DeckData $deck) use ($card_repository, $user, $deck_repository, $material_repository) {
            return $this->buildDeckStatsWithShortcode($deck, $card_repository, $user, $deck_repository, $material_repository);
        });

        // Build stats for individually enrolled child decks
        $individual_child_stats = $individual_child_decks->map(function (DeckData $deck) use ($card_repository, $user, $deck_repository, $material_repository) {
            return $this->buildDeckStatsWithShortcode($deck, $card_repository, $user, $deck_repository, $material_repository);
        });

        // Build stats for available decks
        $available_with_stats = $available_decks->map(function (DeckData $deck) use ($card_repository, $user, $deck_repository, $material_repository) {
            return $this->buildDeckStats($deck, $card_repository, $user, $deck_repository, $material_repository);
        });

        // Apply category filter if selected
        if ($this->categoryId !== null) {
            $enrolled_with_stats = $enrolled_with_stats->filter(function ($deck_stat) {
                return $this->matchesCategory($deck_stat);
            });

            $individual_child_stats = $individual_child_stats->filter(function ($deck_stat) {
                return $this->matchesCategory($deck_stat);
            });

            $available_with_stats = $available_with_stats->filter(function ($deck_stat) {
                return $this->matchesCategory($deck_stat);
            });
        }

        // Separate collections and standalone decks for enrolled
        $enrolled_collections = $enrolled_with_stats->filter(fn ($stat) => $stat['isParent']);
        $enrolled_standalone = $enrolled_with_stats->filter(fn ($stat) => ! $stat['isParent'] && $stat['deck']->parent_deck_id === null);

        // Combine standalone decks with individually enrolled child decks for "My Decks" section
        $all_my_decks = $enrolled_standalone->merge($individual_child_stats);

        // Separate available collections and standalone
        $available_collections = $available_with_stats->filter(fn ($stat) => $stat['isParent']);
        $available_standalone = $available_with_stats->filter(fn ($stat) => ! $stat['isParent']);

        return view('livewire.library', [
            'enrolledCollections' => $enrolled_collections,
            'enrolledStandalone' => $all_my_decks,
            'availableCollections' => $available_collections,
            'availableStandalone' => $available_standalone,
            'categories' => $categories,
            'selectedCategoryId' => $this->categoryId,
        ]);
    }

    private function matchesCategory(array $deck_stat): bool
    {
        $deck = $deck_stat['deck'];

        // Check if deck has this category
        if ($deck->category_ids && in_array($this->categoryId, $deck->category_ids)) {
            return true;
        }

        // For collections, check if any children have this category
        if ($deck_stat['isParent'] && ! empty($deck_stat['children'])) {
            foreach ($deck_stat['children'] as $child_stat) {
                $child_deck = $child_stat['deck'];
                if ($child_deck->category_ids && in_array($this->categoryId, $child_deck->category_ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildDeckStatsWithShortcode(
        DeckData $deck,
        CardRepository $card_repository,
        $user,
        DeckRepository $deck_repository,
        MaterialRepository $material_repository
    ): array {
        $stats = $this->buildDeckStats($deck, $card_repository, $user, $deck_repository, $material_repository);

        // Get shortcode if this is a standalone deck (not a collection)
        if (! $deck->is_collection) {
            $user_model = \Domain\User\Models\User::find($user->id);
            $enrolled_deck = $user_model->enrolledDecks()
                ->where('deck_id', $deck->id)
                ->first();

            $stats['shortcode'] = $enrolled_deck ? $enrolled_deck->pivot->shortcode : null;
        } else {
            $stats['shortcode'] = null;
        }

        return $stats;
    }

    private function buildDeckStats(
        DeckData $deck,
        CardRepository $card_repository,
        $user,
        DeckRepository $deck_repository,
        MaterialRepository $material_repository
    ): array {
        $is_parent = $deck->is_collection;
        $children = $deck->children;

        if ($is_parent && $children !== null && $children->isNotEmpty()) {
            // For parent decks, aggregate stats from all children
            $total_cards = 0;
            $stage_sum = 0;
            $child_stats = [];

            foreach ($children as $child_deck) {
                $child_card_stats = $this->getCardStats($child_deck, $card_repository, $user);
                $total_cards += $child_card_stats['totalCards'];
                $stage_sum += $child_card_stats['stageSum'];

                $is_child_enrolled = $deck_repository->isUserEnrolledInDeck($user->id, $child_deck->id);

                $child_stats[] = [
                    'deck' => $child_deck,
                    'totalCards' => $child_card_stats['totalCards'],
                    'reviewedCount' => $child_card_stats['reviewedCount'],
                    'progress' => $child_card_stats['progress'],
                    'isEnrolled' => $is_child_enrolled,
                    'image' => DeckImageHelper::getImagePath($child_deck),
                    'hasMaterials' => $material_repository->hasMaterials($child_deck->id),
                ];
            }

            // Check if user is enrolled in parent (which means enrolled in all children)
            $is_enrolled = $deck_repository->isUserEnrolledInDeck($user->id, $deck->id);

            // Calculate stage-based progress for collection
            $progress = $total_cards > 0
                ? (int) round(($stage_sum / SrsStage::STAGE_MAX / $total_cards) * 100)
                : 0;

            return [
                'deck' => $deck,
                'totalCards' => $total_cards,
                'reviewedCount' => $total_cards - $this->getNewCardsCount($children, $card_repository, $user),
                'progress' => $progress,
                'isEnrolled' => $is_enrolled,
                'isParent' => true,
                'children' => $child_stats,
                'childCount' => count($child_stats),
                'image' => DeckImageHelper::getImagePath($deck),
                'hasMaterials' => false, // Collections don't have materials directly
            ];
        }

        // Standalone deck (or collection without children yet)
        $card_stats = $this->getCardStats($deck, $card_repository, $user);
        $is_enrolled = $deck_repository->isUserEnrolledInDeck($user->id, $deck->id);

        return [
            'deck' => $deck,
            'totalCards' => $card_stats['totalCards'],
            'reviewedCount' => $card_stats['reviewedCount'],
            'progress' => $card_stats['progress'],
            'isEnrolled' => $is_enrolled,
            'isParent' => $is_parent,
            'children' => [],
            'childCount' => 0,
            'image' => DeckImageHelper::getImagePath($deck),
            'hasMaterials' => $material_repository->hasMaterials($deck->id),
        ];
    }

    private function getCardStats(DeckData $deck, CardRepository $card_repository, $user): array
    {
        $cards = $card_repository->getByDeckId($deck->id);
        $total_cards = $cards->count();

        $reviewed_card_ids = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();
        $reviewed_count = $cards->filter(fn ($card) => in_array($card->id, $reviewed_card_ids))->count();

        // Calculate stage-based progress
        $card_ids = $cards->pluck('id')->toArray();
        $stage_sum = CardReview::where('user_id', $user->id)
            ->whereIn('card_id', $card_ids)
            ->sum('srs_stage');

        $progress = $total_cards > 0
            ? (int) round(($stage_sum / SrsStage::STAGE_MAX / $total_cards) * 100)
            : 0;

        return [
            'totalCards' => $total_cards,
            'reviewedCount' => $reviewed_count,
            'stageSum' => $stage_sum,
            'progress' => $progress,
        ];
    }

    private function getNewCardsCount($children, CardRepository $card_repository, $user): int
    {
        $reviewed_card_ids = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();

        $new_count = 0;
        foreach ($children as $child_deck) {
            $cards = $card_repository->getByDeckId($child_deck->id);
            $new_count += $cards->filter(fn ($card) => ! in_array($card->id, $reviewed_card_ids))->count();
        }

        return $new_count;
    }
}
