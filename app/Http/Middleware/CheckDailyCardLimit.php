<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Subscription\Repositories\PlanRepository;
use Domain\Subscription\Repositories\SubscriptionRepository;
use Domain\User\Repositories\UserRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDailyCardLimit
{
    public function __construct(
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private PlanRepository $planRepository,
        private CardReviewRepository $cardReviewRepository
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->userRepository->getLoggedInUser();
        $subscription = $this->subscriptionRepository->findByUserId($user->id);

        // Determine daily limit based on subscription
        $dailyLimit = $this->getDailyLimit($subscription);

        // If unlimited, allow
        if ($dailyLimit === -1) {
            return $next($request);
        }

        // Get today's review count
        $todayReviews = $this->cardReviewRepository->getRecentActivity($user->id, 1000)
            ->filter(function ($activity) {
                $reviewDate = $activity->reviewed_at ? \Carbon\Carbon::parse($activity->reviewed_at) : null;

                return $reviewDate && $reviewDate->isToday();
            })
            ->count();

        // Check if limit reached
        if ($todayReviews >= $dailyLimit) {
            return redirect()->route('dashboard')
                ->with('error', "You've reached your daily limit of {$dailyLimit} card reviews. Upgrade your plan for more!");
        }

        return $next($request);
    }

    private function getDailyLimit($subscription): int
    {
        // No subscription: free tier with limit
        if (! $subscription || $subscription->status !== 'active') {
            return 10; // Free tier: 10 cards per day
        }

        // Get plan
        $plan = $this->planRepository->findById($subscription->plan_id);

        if (! $plan) {
            return 10; // Default to free tier
        }

        // Plan-based limits
        return match ($plan->name) {
            'Basic' => 50,      // Basic plan: 50 cards per day
            'Premium' => -1,    // Premium plan: unlimited
            default => 10,      // Unknown plan: default to free tier
        };
    }
}
