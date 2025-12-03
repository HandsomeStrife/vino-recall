<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Subscription\Repositories\PlanRepository;
use Domain\Subscription\Repositories\SubscriptionRepository;
use Domain\User\Repositories\UserRepository;
use Livewire\Component;

class SubscriptionManagement extends Component
{
    public function render(
        UserRepository $userRepository,
        PlanRepository $planRepository,
        SubscriptionRepository $subscriptionRepository
    ) {
        $user = $userRepository->getLoggedInUser();
        $plans = $planRepository->getAll();
        $subscription = $subscriptionRepository->findByUserId($user->id);

        return view('livewire.subscription-management', [
            'plans' => $plans,
            'subscription' => $subscription,
        ]);
    }
}
