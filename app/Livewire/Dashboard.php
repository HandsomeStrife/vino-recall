<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Data\DeckData;
use Domain\Deck\Helpers\DeckImageHelper;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Dashboard extends Component
{
    private function formatTimeUntil(\Carbon\Carbon $datetime): string
    {
        $now = now();
        $diff = $now->diff($datetime);

        if ($diff->d > 0) {
            if ($diff->h > 0) {
                return $diff->d.' day'.($diff->d > 1 ? 's' : '').' '.$diff->h.' hour'.($diff->h > 1 ? 's' : '');
            }

            return $diff->d.' day'.($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            if ($diff->i > 0) {
                return $diff->h.' hour'.($diff->h > 1 ? 's' : '').' '.$diff->i.' minute'.($diff->i > 1 ? 's' : '');
            }

            return $diff->h.' hour'.($diff->h > 1 ? 's' : '');
        } else {
            $minutes = max(1, $diff->i);

            return $minutes.' minute'.($minutes > 1 ? 's' : '');
        }
    }

    public function render(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository,
        DeckRepository $deckRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $allEnrolledDecks = $deckRepository->getUserEnrolledDecks($user->id);
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);
        $streak = $cardReviewRepository->getCurrentStreak($user->id);
        $recentActivity = $cardReviewRepository->getRecentActivity($user->id, 5);
        $mistakes = $cardReviewRepository->getMistakes($user->id, 5);
        $availableDecks = $deckRepository->getAvailableDecks($user->id);

        // Daily goal: Review 20 cards (from review_history, excluding practice)
        $dailyGoal = 20;
        $todayReviews = ReviewHistory::where('user_id', $user->id)
            ->where('is_practice', false)
            ->whereDate('reviewed_at', today())
            ->count();
        $dailyGoalProgress = min(100, (int) (($todayReviews / $dailyGoal) * 100));

        // Week activity for streak visualization
        $weekActivity = collect();
        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $dayReviews = ReviewHistory::where('user_id', $user->id)
                ->where('is_practice', false)
                ->whereDate('reviewed_at', $date->toDateString())
                ->count();

            $weekActivity->push([
                'date' => $date,
                'completed' => $dayReviews >= $dailyGoal,
            ]);
        }

        // Get cards for recent activity and mistakes (both now use ReviewHistoryData)
        $recentActivityWithCards = $recentActivity->map(function ($activity) use ($cardRepository) {
            $card = $cardRepository->findById($activity->card_id);

            return [
                'activity' => $activity,
                'card' => $card,
            ];
        });

        $mistakesWithCards = $mistakes->map(function ($history) use ($cardRepository) {
            $card = $cardRepository->findById($history->card_id);

            return [
                'history' => $history,
                'card' => $card,
            ];
        });

        // Separate enrolled decks into collections and standalone
        $enrolledCollections = $allEnrolledDecks->filter(fn ($deck) => $deck->is_collection);
        $enrolledStandalone = $allEnrolledDecks->filter(fn ($deck) => ! $deck->is_collection && $deck->parent_deck_id === null);

        // Build collection stats
        $collectionsWithStats = $enrolledCollections->map(function ($collection) use ($cardRepository, $cardReviewRepository, $user, $dueCards, $allEnrolledDecks) {
            return $this->buildCollectionStats($collection, $cardRepository, $cardReviewRepository, $user, $dueCards, $allEnrolledDecks);
        })->sortBy(function ($stat) {
            // Sort by due cards descending
            return $stat['dueCards'] > 0 ? [1, -$stat['dueCards']] : [2, 0];
        })->values();

        // Build standalone deck stats
        $standaloneWithStats = $enrolledStandalone->map(function ($deck) use ($cardRepository, $cardReviewRepository, $user, $dueCards) {
            return $this->buildDeckStats($deck, $cardRepository, $cardReviewRepository, $user, $dueCards);
        })->sortBy(function ($stat) {
            return $stat['dueCards'] > 0 ? [1, -$stat['dueCards']] : [2, 0];
        })->values();

        // Merge collections and standalone for hero display (prioritize by due cards)
        $allItemsWithStats = $collectionsWithStats->merge($standaloneWithStats)
            ->sortBy(function ($stat) {
                return $stat['dueCards'] > 0 ? [1, -$stat['dueCards']] : [2, 0];
            })->values();

        // Take top 2 items for hero
        $heroItems = $allItemsWithStats->take(2);

        // Remaining items for sidebar
        $otherItems = $allItemsWithStats->skip(2);

        $hasEnrolledDecks = $enrolledCollections->isNotEmpty() || $enrolledStandalone->isNotEmpty();

        // Calculate total new cards across all enrolled decks
        $totalNewCards = $allItemsWithStats->sum('newCards');

        // Extract first name only for welcome message
        $firstName = explode(' ', $user->name)[0];

        return view('livewire.dashboard', [
            'userName' => $firstName,
            'heroItems' => $heroItems,
            'otherItems' => $otherItems,
            'hasEnrolledDecks' => $hasEnrolledDecks,
            'dueCardsCount' => $dueCards->count(),
            'newCardsCount' => $totalNewCards,
            'streak' => $streak,
            'recentActivityWithCards' => $recentActivityWithCards,
            'mistakesWithCards' => $mistakesWithCards,
            'availableDecks' => $availableDecks,
            'dailyGoal' => $dailyGoal,
            'todayReviews' => $todayReviews,
            'dailyGoalProgress' => $dailyGoalProgress,
            'weekActivity' => $weekActivity,
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

        // Get all card review records for user (SRS state)
        $reviewedCardIds = CardReview::where('user_id', $user->id)
            ->pluck('card_id')
            ->toArray();

        foreach ($childDecks as $childDeck) {
            $cards = $cardRepository->getByDeckId($childDeck->id);
            $totalCards += $cards->count();

            // Sum stages for this child deck
            $childCardIds = $cards->pluck('id')->toArray();
            $stageSum += CardReview::where('user_id', $user->id)
                ->whereIn('card_id', $childCardIds)
                ->sum('srs_stage');

            // Count due cards for this child
            $dueCardsCount += $dueCards->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            })->count();

            // Count new cards for this child (not in card_reviews)
            $newCardsCount += $cards->filter(fn ($card) => ! in_array($card->id, $reviewedCardIds))->count();
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

        $newCardsForDeck = $cards->filter(fn ($card) => ! in_array($card->id, $reviewedCardIds))->count();

        // Use stage-based progress
        $progress = $cardReviewRepository->getProgress($user->id, $deck->id, $totalCards);

        // Use accuracy from review history
        $accuracyRate = $cardReviewRepository->getAccuracy($user->id, $deck->id);

        $nextReviewTime = null;
        if ($dueCardsForDeck === 0 && $reviewedCount > 0) {
            $cardIds = $cards->pluck('id')->toArray();
            $nextReview = CardReview::where('user_id', $user->id)
                ->whereIn('card_id', $cardIds)
                ->where('next_review_at', '>', now())
                ->where('srs_stage', '<', SrsStage::STAGE_MAX) // Not retired
                ->orderBy('next_review_at', 'asc')
                ->first();

            if ($nextReview) {
                $nextReviewTime = $this->formatTimeUntil($nextReview->next_review_at);
            }
        }

        return [
            'type' => 'deck',
            'deck' => $deck,
            'totalCards' => $totalCards,
            'reviewedCount' => $reviewedCount,
            'dueCards' => $dueCardsForDeck,
            'newCards' => $newCardsForDeck,
            'progress' => (int) round($progress),
            'retentionRate' => $accuracyRate,
            'nextReviewTime' => $nextReviewTime,
            'image' => DeckImageHelper::getImagePath($deck),
        ];
    }
}
