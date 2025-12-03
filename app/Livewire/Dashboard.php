<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Card\Repositories\CardRepository;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(
        UserRepository $userRepository,
        CardReviewRepository $cardReviewRepository,
        CardRepository $cardRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $dueCards = $cardReviewRepository->getDueCardsForUser($user->id);
        $masteredCount = $cardReviewRepository->getMasteredCardsCount($user->id);
        $streak = $cardReviewRepository->getCurrentStreak($user->id);
        $recentActivity = $cardReviewRepository->getRecentActivity($user->id, 5);

        // Calculate time spent (rough estimate: 30 seconds per review)
        $totalReviews = $cardReviewRepository->getUserReviews($user->id)->count();
        $timeSpentMinutes = (int) ($totalReviews * 0.5);
        $timeSpentHours = (int) ($timeSpentMinutes / 60);
        $timeSpentMinutesRemainder = $timeSpentMinutes % 60;

        // Daily goal: Review 20 cards
        $dailyGoal = 20;
        $todayReviews = $cardReviewRepository->getRecentActivity($user->id, 100)
            ->filter(function ($activity) {
                $reviewDate = $activity->created_at ? \Carbon\Carbon::parse($activity->created_at) : null;

                return $reviewDate && $reviewDate->isToday();
            })
            ->count();
        $dailyGoalProgress = min(100, (int) (($todayReviews / $dailyGoal) * 100));

        // Get cards for recent activity
        $recentActivityWithCards = $recentActivity->map(function ($activity) use ($cardRepository) {
            $card = $cardRepository->findById($activity->card_id);

            return [
                'activity' => $activity,
                'card' => $card,
            ];
        });

        // Get total cards count for mastery percentage
        $totalCards = $cardRepository->getAll()->count();
        $masteryPercentage = $totalCards > 0 ? (int) (($masteredCount / $totalCards) * 100) : 0;

        return view('livewire.dashboard', [
            'dueCardsCount' => $dueCards->count(),
            'masteredCount' => $masteredCount,
            'streak' => $streak,
            'timeSpent' => [
                'hours' => $timeSpentHours,
                'minutes' => $timeSpentMinutesRemainder,
            ],
            'recentActivityWithCards' => $recentActivityWithCards,
            'dailyGoal' => $dailyGoal,
            'todayReviews' => $todayReviews,
            'dailyGoalProgress' => $dailyGoalProgress,
            'masteryPercentage' => $masteryPercentage,
            'totalCards' => $totalCards,
        ]);
    }
}
