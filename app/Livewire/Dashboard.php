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
    private function formatTimeUntil(\Carbon\Carbon $datetime): string
    {
        $now = now();
        $diff = $now->diff($datetime);
        
        if ($diff->d > 0) {
            // Days and hours
            if ($diff->h > 0) {
                return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ' . $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
            }
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            // Hours and minutes
            if ($diff->i > 0) {
                return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
            }
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } else {
            // Just minutes
            $minutes = max(1, $diff->i); // Show at least 1 minute
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
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
        
        // Filter out parent decks (they have no cards, just containers)
        // Only show decks that actually have cards for study
        $enrolledDecks = $allEnrolledDecks->filter(function ($deck) {
            return !$deck->is_collection;
        });
        
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

        // Get deck stats for enrolled decks (excluding parent containers)
        $decksWithStats = $enrolledDecks->map(function ($deck) use ($cardRepository, $cardReviewRepository, $user, $dueCards) {
            $cards = $cardRepository->getByDeckId($deck->id);
            $totalCards = $cards->count();

            // Count reviewed cards
            $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $user->id)
                ->where('is_practice', false)
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

            // Get next review time if no cards are due
            $nextReviewTime = null;
            if ($dueCardsForDeck === 0 && $reviewedCount > 0) {
                $cardIds = $cards->pluck('id')->toArray();
                $nextReview = \Domain\Card\Models\CardReview::where('user_id', $user->id)
                    ->where('is_practice', false)
                    ->whereIn('card_id', $cardIds)
                    ->where('next_review_at', '>', now())
                    ->orderBy('next_review_at', 'asc')
                    ->first();
                
                if ($nextReview) {
                    $nextReviewTime = $this->formatTimeUntil($nextReview->next_review_at);
                }
            }

            return [
                'deck' => $deck,
                'totalCards' => $totalCards,
                'reviewedCount' => $reviewedCount,
                'dueCards' => $dueCardsForDeck,
                'newCards' => $newCardsForDeck,
                'progress' => $totalCards > 0 ? (int) (($reviewedCount / $totalCards) * 100) : 0,
                'retentionRate' => $retentionRate,
                'nextReviewTime' => $nextReviewTime,
                'image' => DeckImageHelper::getImagePath($deck),
                'enrolledAt' => $deck->pivot->enrolled_at ?? now(),
                'parentName' => $deck->parent_name,
            ];
        })->sortBy(function ($deckStat) {
            // Priority sorting:
            // 1. Decks with due cards (most urgent)
            // 2. Recently started decks with new cards (< 7 days, has progress)
            // 3. Newly enrolled decks (< 7 days, no progress)
            // 4. Other decks
            
            $enrolledDays = \Carbon\Carbon::parse($deckStat['enrolledAt'])->diffInDays(now());
            $hasProgress = $deckStat['reviewedCount'] > 0;
            
            if ($deckStat['dueCards'] > 0) {
                // Priority 1: Most due cards first
                return [1, -$deckStat['dueCards']];
            } elseif ($enrolledDays <= 7 && $hasProgress) {
                // Priority 2: Recently started (newest first)
                return [2, $enrolledDays];
            } elseif ($enrolledDays <= 7) {
                // Priority 3: Newly enrolled (newest first)
                return [3, $enrolledDays];
            } else {
                // Priority 4: Older decks (newest first)
                return [4, $enrolledDays];
            }
        })->values();

        // Take top 2 decks for hero
        $heroDecks = $decksWithStats->take(2);
        
        // Remaining decks for sidebar
        $otherDecks = $decksWithStats->skip(2);

        return view('livewire.dashboard', [
            'userName' => $user->name,
            'enrolledDecks' => $decksWithStats,
            'heroDecks' => $heroDecks,
            'otherDecks' => $otherDecks,
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
