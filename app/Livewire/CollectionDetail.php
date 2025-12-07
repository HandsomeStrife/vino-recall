<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Data\DeckData;
use Domain\Deck\Helpers\DeckImageHelper;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class CollectionDetail extends Component
{
    public int $collectionId;

    public function mount(int $collectionId): void
    {
        $this->collectionId = $collectionId;
    }

    public function render(
        \Domain\Deck\Repositories\DeckRepository $deckRepository,
        CardRepository $cardRepository,
        CardReviewRepository $cardReviewRepository,
        UserRepository $userRepository
    ) {
        $userData = $userRepository->getLoggedInUser();
        $user = User::find($userData->id);

        // Get the collection
        $collection = $deckRepository->findById($this->collectionId);

        if ($collection === null || !$collection->is_collection) {
            abort(404, 'Collection not found');
        }

        // Check if user is enrolled in this collection
        $isEnrolled = $deckRepository->isUserEnrolledInDeck($user->id, $collection->id);

        // Get child decks with user enrollment info (to get shortcodes)
        $childDecks = Deck::where('parent_deck_id', $this->collectionId)
            ->where('is_active', true)
            ->get();

        // Get user's enrolled decks to find shortcodes
        $userEnrollments = $user->enrolledDecks()
            ->whereIn('deck_id', $childDecks->pluck('id'))
            ->get()
            ->keyBy('id');

        // Build stats for each child deck
        $childDecksWithStats = $childDecks->map(function (Deck $deck) use ($cardRepository, $cardReviewRepository, $user, $deckRepository, $userEnrollments) {
            $enrollment = $userEnrollments->get($deck->id);
            $shortcode = $enrollment ? $enrollment->pivot->shortcode : null;

            return $this->buildChildDeckStats($deck, $shortcode, $cardRepository, $cardReviewRepository, $user, $deckRepository);
        })->sortBy(function ($stat) {
            // Sort alphabetically by deck name
            return $stat['deck']->name;
        })->values();

        // Aggregate collection stats with stage-based progress
        $totalCards = $childDecksWithStats->sum('totalCards');
        $stageSum = $childDecksWithStats->sum('stageSum');
        $dueCards = $childDecksWithStats->sum('dueCards');
        $newCards = $childDecksWithStats->sum('newCards');

        // Calculate stage-based progress
        $progress = $totalCards > 0
            ? (int) round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100)
            : 0;

        return view('livewire.collection-detail', [
            'collection' => $collection,
            'childDecksWithStats' => $childDecksWithStats,
            'isEnrolled' => $isEnrolled,
            'totalCards' => $totalCards,
            'dueCards' => $dueCards,
            'newCards' => $newCards,
            'progress' => $progress,
            'image' => DeckImageHelper::getImagePath($collection),
        ]);
    }

    private function buildChildDeckStats(
        Deck $deck,
        ?string $shortcode,
        CardRepository $cardRepository,
        CardReviewRepository $cardReviewRepository,
        $user,
        $deckRepository
    ): array {
        $cards = $cardRepository->getByDeckId($deck->id);
        $totalCards = $cards->count();

        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();
        $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

        // Calculate stage sum for progress
        $cardIds = $cards->pluck('id')->toArray();
        $stageSum = CardReview::where('user_id', $user->id)
            ->whereIn('card_id', $cardIds)
            ->sum('srs_stage');

        // Get due cards
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);
        $dueCardsForDeck = $dueCards->filter(function ($review) use ($cards) {
            return $cards->contains(fn ($card) => $card->id === $review->card_id);
        })->count();

        $newCardsForDeck = $cards->filter(fn ($card) => !in_array($card->id, $reviewedCardIds))->count();

        // Use accuracy from review history
        $accuracyRate = $cardReviewRepository->getAccuracy($user->id, $deck->id);
        $isEnrolled = $shortcode !== null;

        // Calculate stage-based progress
        $progress = $totalCards > 0
            ? (int) round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100)
            : 0;

        return [
            'deck' => $deck,
            'shortcode' => $shortcode,
            'totalCards' => $totalCards,
            'reviewedCount' => $reviewedCount,
            'stageSum' => $stageSum,
            'dueCards' => $dueCardsForDeck,
            'newCards' => $newCardsForDeck,
            'progress' => $progress,
            'retentionRate' => $accuracyRate,
            'isEnrolled' => $isEnrolled,
            'image' => DeckImageHelper::getImagePath(DeckData::fromModel($deck)),
        ];
    }
}
