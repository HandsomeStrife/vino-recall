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
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Library extends Component
{
    public ?int $categoryId = null;

    public string $activeTab = 'enrolled';

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
        DeckRepository $deckRepository,
        CardRepository $cardRepository,
        UserRepository $userRepository,
        CategoryRepository $categoryRepository
    ) {
        $user = $userRepository->getLoggedInUser();

        // Get all categories for the filter
        $categories = $categoryRepository->getAll();

        // Get enrolled decks and available decks
        $enrolledDecks = $deckRepository->getUserEnrolledDecks($user->id);
        $availableDecks = $deckRepository->getAvailableDecks($user->id);

        // Build stats for enrolled decks
        $enrolledWithStats = $enrolledDecks->map(function (DeckData $deck) use ($cardRepository, $user, $deckRepository) {
            return $this->buildDeckStatsWithShortcode($deck, $cardRepository, $user, $deckRepository);
        });

        // Build stats for available decks
        $availableWithStats = $availableDecks->map(function (DeckData $deck) use ($cardRepository, $user, $deckRepository) {
            return $this->buildDeckStats($deck, $cardRepository, $user, $deckRepository);
        });

        // Apply category filter if selected
        if ($this->categoryId !== null) {
            $enrolledWithStats = $enrolledWithStats->filter(function ($deckStat) {
                return $this->matchesCategory($deckStat);
            });

            $availableWithStats = $availableWithStats->filter(function ($deckStat) {
                return $this->matchesCategory($deckStat);
            });
        }

        // Separate collections and standalone decks for both enrolled and available
        $enrolledCollections = $enrolledWithStats->filter(fn ($stat) => $stat['isParent']);
        $enrolledStandalone = $enrolledWithStats->filter(fn ($stat) => ! $stat['isParent']);
        $availableCollections = $availableWithStats->filter(fn ($stat) => $stat['isParent']);
        $availableStandalone = $availableWithStats->filter(fn ($stat) => ! $stat['isParent']);

        return view('livewire.library', [
            'enrolledCollections' => $enrolledCollections,
            'enrolledStandalone' => $enrolledStandalone,
            'availableCollections' => $availableCollections,
            'availableStandalone' => $availableStandalone,
            'categories' => $categories,
            'selectedCategoryId' => $this->categoryId,
        ]);
    }

    private function matchesCategory(array $deckStat): bool
    {
        $deck = $deckStat['deck'];

        // Check if deck has this category
        if ($deck->category_ids && in_array($this->categoryId, $deck->category_ids)) {
            return true;
        }

        // For collections, check if any children have this category
        if ($deckStat['isParent'] && ! empty($deckStat['children'])) {
            foreach ($deckStat['children'] as $childStat) {
                $childDeck = $childStat['deck'];
                if ($childDeck->category_ids && in_array($this->categoryId, $childDeck->category_ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildDeckStatsWithShortcode(
        DeckData $deck,
        CardRepository $cardRepository,
        $user,
        DeckRepository $deckRepository
    ): array {
        $stats = $this->buildDeckStats($deck, $cardRepository, $user, $deckRepository);

        // Get shortcode if this is a standalone deck (not a collection)
        if (! $deck->is_collection) {
            $userModel = \Domain\User\Models\User::find($user->id);
            $enrolledDeck = $userModel->enrolledDecks()
                ->where('deck_id', $deck->id)
                ->first();

            $stats['shortcode'] = $enrolledDeck ? $enrolledDeck->pivot->shortcode : null;
        } else {
            $stats['shortcode'] = null;
        }

        return $stats;
    }

    private function buildDeckStats(
        DeckData $deck,
        CardRepository $cardRepository,
        $user,
        DeckRepository $deckRepository
    ): array {
        $isParent = $deck->is_collection;
        $children = $deck->children;

        if ($isParent && $children !== null && $children->isNotEmpty()) {
            // For parent decks, aggregate stats from all children
            $totalCards = 0;
            $stageSum = 0;
            $childStats = [];

            foreach ($children as $childDeck) {
                $childCardStats = $this->getCardStats($childDeck, $cardRepository, $user);
                $totalCards += $childCardStats['totalCards'];
                $stageSum += $childCardStats['stageSum'];

                $isChildEnrolled = $deckRepository->isUserEnrolledInDeck($user->id, $childDeck->id);

                $childStats[] = [
                    'deck' => $childDeck,
                    'totalCards' => $childCardStats['totalCards'],
                    'reviewedCount' => $childCardStats['reviewedCount'],
                    'progress' => $childCardStats['progress'],
                    'isEnrolled' => $isChildEnrolled,
                    'image' => DeckImageHelper::getImagePath($childDeck),
                ];
            }

            // Check if user is enrolled in parent (which means enrolled in all children)
            $isEnrolled = $deckRepository->isUserEnrolledInDeck($user->id, $deck->id);

            // Calculate stage-based progress for collection
            $progress = $totalCards > 0
                ? (int) round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100)
                : 0;

            return [
                'deck' => $deck,
                'totalCards' => $totalCards,
                'reviewedCount' => $totalCards - $this->getNewCardsCount($children, $cardRepository, $user),
                'progress' => $progress,
                'isEnrolled' => $isEnrolled,
                'isParent' => true,
                'children' => $childStats,
                'childCount' => count($childStats),
                'image' => DeckImageHelper::getImagePath($deck),
            ];
        }

        // Standalone deck (or collection without children yet)
        $cardStats = $this->getCardStats($deck, $cardRepository, $user);
        $isEnrolled = $deckRepository->isUserEnrolledInDeck($user->id, $deck->id);

        return [
            'deck' => $deck,
            'totalCards' => $cardStats['totalCards'],
            'reviewedCount' => $cardStats['reviewedCount'],
            'progress' => $cardStats['progress'],
            'isEnrolled' => $isEnrolled,
            'isParent' => $isParent,
            'children' => [],
            'childCount' => 0,
            'image' => DeckImageHelper::getImagePath($deck),
        ];
    }

    private function getCardStats(DeckData $deck, CardRepository $cardRepository, $user): array
    {
        $cards = $cardRepository->getByDeckId($deck->id);
        $totalCards = $cards->count();

        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();
        $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

        // Calculate stage-based progress
        $cardIds = $cards->pluck('id')->toArray();
        $stageSum = CardReview::where('user_id', $user->id)
            ->whereIn('card_id', $cardIds)
            ->sum('srs_stage');

        $progress = $totalCards > 0
            ? (int) round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100)
            : 0;

        return [
            'totalCards' => $totalCards,
            'reviewedCount' => $reviewedCount,
            'stageSum' => $stageSum,
            'progress' => $progress,
        ];
    }

    private function getNewCardsCount($children, CardRepository $cardRepository, $user): int
    {
        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();

        $newCount = 0;
        foreach ($children as $childDeck) {
            $cards = $cardRepository->getByDeckId($childDeck->id);
            $newCount += $cards->filter(fn ($card) => ! in_array($card->id, $reviewedCardIds))->count();
        }

        return $newCount;
    }
}
