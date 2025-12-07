<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Data\DeckData;
use Domain\Deck\Helpers\DeckImageHelper;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Enrolled extends Component
{
    public function render(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository,
        DeckRepository $deckRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $allEnrolledDecks = $deckRepository->getUserEnrolledDecks($user->id);
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);

        // Separate enrolled decks into collections and standalone
        $enrolledCollections = $allEnrolledDecks->filter(fn ($deck) => $deck->is_collection);
        $enrolledStandalone = $allEnrolledDecks->filter(fn ($deck) => !$deck->is_collection && $deck->parent_deck_id === null);

        // Build collection stats
        $collectionsWithStats = $enrolledCollections->map(function ($collection) use ($cardRepository, $cardReviewRepository, $user, $dueCards, $allEnrolledDecks) {
            return $this->buildCollectionStats($collection, $cardRepository, $cardReviewRepository, $user, $dueCards, $allEnrolledDecks);
        })->sortByDesc(fn ($stat) => $stat['dueCards'])->values();

        // Build standalone deck stats
        $standaloneWithStats = $enrolledStandalone->map(function ($deck) use ($cardRepository, $cardReviewRepository, $user, $dueCards) {
            return $this->buildDeckStats($deck, $cardRepository, $cardReviewRepository, $user, $dueCards);
        })->sortByDesc(fn ($stat) => $stat['dueCards'])->values();

        $hasEnrolledDecks = $enrolledCollections->isNotEmpty() || $enrolledStandalone->isNotEmpty();

        return view('livewire.enrolled', [
            'collectionsWithStats' => $collectionsWithStats,
            'standaloneWithStats' => $standaloneWithStats,
            'hasEnrolledDecks' => $hasEnrolledDecks,
            'totalDueCards' => $dueCards->count(),
        ]);
    }

    private function buildCollectionStats(
        DeckData $collection,
        CardRepository $cardRepository,
        CardReviewRepository $cardReviewRepository,
        $user,
        $dueCards,
        $allEnrolledDecks
    ): array {
        // Get all child decks the user is enrolled in for this collection
        $childDecks = $allEnrolledDecks->filter(fn ($deck) => $deck->parent_deck_id === $collection->id);

        $totalCards = 0;
        $stageSum = 0;
        $dueCardsCount = 0;
        $newCardsCount = 0;
        $childCount = $childDecks->count();

        // Get all reviewed card IDs for user
        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();

        foreach ($childDecks as $childDeck) {
            $cards = $cardRepository->getByDeckId($childDeck->id);
            $totalCards += $cards->count();

            // Sum stages for progress calculation
            $cardIds = $cards->pluck('id')->toArray();
            $stageSum += CardReview::where('user_id', $user->id)
                ->whereIn('card_id', $cardIds)
                ->sum('srs_stage');

            // Count due cards for this child
            $dueCardsCount += $dueCards->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            })->count();

            // Count new cards for this child
            $newCardsCount += $cards->filter(fn ($card) => !in_array($card->id, $reviewedCardIds))->count();
        }

        // Calculate stage-based progress
        $progress = $totalCards > 0
            ? (int) round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100)
            : 0;

        return [
            'type' => 'collection',
            'deck' => $collection,
            'totalCards' => $totalCards,
            'reviewedCount' => $totalCards - $newCardsCount,
            'dueCards' => $dueCardsCount,
            'newCards' => $newCardsCount,
            'progress' => $progress,
            'childCount' => $childCount,
            'image' => DeckImageHelper::getImagePath($collection),
        ];
    }

    private function buildDeckStats(
        DeckData $deck,
        CardRepository $cardRepository,
        CardReviewRepository $cardReviewRepository,
        $user,
        $dueCards
    ): array {
        $cards = $cardRepository->getByDeckId($deck->id);
        $totalCards = $cards->count();

        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();
        $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

        $dueCardsForDeck = $dueCards->filter(function ($review) use ($cards) {
            return $cards->contains(fn ($card) => $card->id === $review->card_id);
        })->count();

        $newCardsForDeck = $cards->filter(fn ($card) => !in_array($card->id, $reviewedCardIds))->count();

        // Use stage-based progress
        $progress = $cardReviewRepository->getProgress($user->id, $deck->id, $totalCards);

        // Use accuracy from review history
        $accuracyRate = $cardReviewRepository->getAccuracy($user->id, $deck->id);

        return [
            'type' => 'deck',
            'deck' => $deck,
            'totalCards' => $totalCards,
            'reviewedCount' => $reviewedCount,
            'dueCards' => $dueCardsForDeck,
            'newCards' => $newCardsForDeck,
            'progress' => (int) round($progress),
            'retentionRate' => $accuracyRate,
            'image' => DeckImageHelper::getImagePath($deck),
        ];
    }
}
