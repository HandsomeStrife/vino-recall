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

    public function filterByCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
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

        // Get active decks (only parent + standalone, with children loaded)
        $decks = $deckRepository->getActive();

        // Get all categories for the filter
        $categories = $categoryRepository->getAll();

        // Build deck stats and apply category filter
        $decksWithStats = $decks->map(function (DeckData $deck) use ($cardRepository, $user, $deckRepository) {
            return $this->buildDeckStats($deck, $cardRepository, $user, $deckRepository);
        });

        // Apply category filter if selected
        if ($this->categoryId !== null) {
            $decksWithStats = $decksWithStats->filter(function ($deckStat) {
                $deck = $deckStat['deck'];

                // Check if deck has this category
                if ($deck->category_ids && in_array($this->categoryId, $deck->category_ids)) {
                    return true;
                }

                // For collections, check if any children have this category
                if ($deckStat['isParent'] && !empty($deckStat['children'])) {
                    foreach ($deckStat['children'] as $childStat) {
                        $childDeck = $childStat['deck'];
                        if ($childDeck->category_ids && in_array($this->categoryId, $childDeck->category_ids)) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        // Separate collections and standalone decks
        $collections = $decksWithStats->filter(fn ($stat) => $stat['isParent']);
        $standaloneDecks = $decksWithStats->filter(fn ($stat) => !$stat['isParent']);

        return view('livewire.library', [
            'collections' => $collections,
            'standaloneDecks' => $standaloneDecks,
            'categories' => $categories,
            'selectedCategoryId' => $this->categoryId,
        ]);
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
            $newCount += $cards->filter(fn ($card) => !in_array($card->id, $reviewedCardIds))->count();
        }

        return $newCount;
    }
}
