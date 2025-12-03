<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Helpers\DeckImageHelper;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository,
        DeckRepository $deckRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $enrolledDecks = $deckRepository->getUserEnrolledDecks($user->id);
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);
        $masteredCount = $cardReviewRepository->getMasteredCardsCount($user->id);
        $streak = $cardReviewRepository->getCurrentStreak($user->id);
        $recentActivity = $cardReviewRepository->getRecentActivity($user->id, 5);
        $mistakes = $cardReviewRepository->getMistakes($user->id, 5);
        $availableDecks = $deckRepository->getAvailableDecks($user->id);

        // Daily goal: Review 20 cards
        $dailyGoal = 20;
        $todayReviews = $cardReviewRepository->getRecentActivity($user->id, 100)
            ->filter(function ($activity) {
                $reviewDate = $activity->created_at ? \Carbon\Carbon::parse($activity->created_at) : null;

                return $reviewDate && $reviewDate->isToday();
            })
            ->count();
        $dailyGoalProgress = min(100, (int) (($todayReviews / $dailyGoal) * 100));

        // Week activity for streak visualization
        $weekActivity = collect();
        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $dayReviews = $cardReviewRepository->getRecentActivity($user->id, 1000)
                ->filter(function ($activity) use ($date) {
                    $reviewDate = $activity->created_at ? \Carbon\Carbon::parse($activity->created_at) : null;

                    return $reviewDate && $reviewDate->isSameDay($date);
                })
                ->count();
            
            $weekActivity->push([
                'date' => $date,
                'completed' => $dayReviews >= $dailyGoal,
            ]);
        }

        // Get cards for recent activity and mistakes
        $recentActivityWithCards = $recentActivity->map(function ($activity) use ($cardRepository) {
            $card = $cardRepository->findById($activity->card_id);

            return [
                'activity' => $activity,
                'card' => $card,
            ];
        });

        $mistakesWithCards = $mistakes->map(function ($review) use ($cardRepository) {
            $card = $cardRepository->findById($review->card_id);

            return [
                'review' => $review,
                'card' => $card,
            ];
        });

        // Get deck stats for enrolled decks (prioritize by due cards for hero)
        $decksWithStats = $enrolledDecks->map(function ($deck) use ($cardRepository, $cardReviewRepository, $user, $dueCards) {
            $cards = $cardRepository->getByDeckId($deck->id);
            $totalCards = $cards->count();

            // Count reviewed cards
            $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $user->id)
                ->pluck('card_id')
                ->toArray();
            $reviewedCount = $cards->filter(fn ($card) => in_array($card->id, $reviewedCardIds))->count();

            // Count due cards for this deck
            $dueCardsForDeck = $dueCards->filter(function ($review) use ($cards) {
                return $cards->contains(fn ($card) => $card->id === $review->card_id);
            })->count();

            // Count new cards for this deck
            $newCardsForDeck = $cards->filter(fn ($card) => !in_array($card->id, $reviewedCardIds))->count();

            // Get retention rate for this deck
            $retentionRate = $cardReviewRepository->getRetentionRate($user->id, $deck->id);

            return [
                'deck' => $deck,
                'totalCards' => $totalCards,
                'reviewedCount' => $reviewedCount,
                'dueCards' => $dueCardsForDeck,
                'newCards' => $newCardsForDeck,
                'progress' => $totalCards > 0 ? (int) (($reviewedCount / $totalCards) * 100) : 0,
                'retentionRate' => $retentionRate,
                'image' => DeckImageHelper::getImagePath($deck),
            ];
        })->sortByDesc('dueCards'); // Sort by due cards for hero display

        // Take top 3 decks for hero
        $heroDecks = $decksWithStats->take(3);

        return view('livewire.dashboard', [
            'userName' => $user->name,
            'enrolledDecks' => $decksWithStats,
            'heroDecks' => $heroDecks,
            'hasEnrolledDecks' => $enrolledDecks->isNotEmpty(),
            'dueCardsCount' => $dueCards->count(),
            'masteredCount' => $masteredCount,
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
}
