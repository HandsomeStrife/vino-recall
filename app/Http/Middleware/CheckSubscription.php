<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Domain\Subscription\Repositories\PlanRepository;
use Domain\Subscription\Repositories\SubscriptionRepository;
use Domain\User\Repositories\UserRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function __construct(
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private PlanRepository $planRepository
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$requiredPlans): Response
    {
        $user = $this->userRepository->getLoggedInUser();
        $subscription = $this->subscriptionRepository->findByUserId($user->id);

        // No subscription required (free access)
        if (empty($requiredPlans)) {
            return $next($request);
        }

        // User has no subscription
        if (! $subscription || $subscription->status !== 'active') {
            return redirect()->route('subscription')
                ->with('error', 'You need an active subscription to access this content.');
        }

        // Get the user's plan
        $userPlan = $this->planRepository->findById($subscription->plan_id);

        if (! $userPlan) {
            return redirect()->route('subscription')
                ->with('error', 'Your subscription plan is invalid.');
        }

        // Check if user's plan is in the required plans list
        if (! in_array($userPlan->name, $requiredPlans)) {
            return redirect()->route('subscription')
                ->with('error', 'Your current plan does not include access to this content.');
        }

        return $next($request);
    }
}
