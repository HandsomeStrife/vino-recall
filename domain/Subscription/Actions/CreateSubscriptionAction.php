<?php

declare(strict_types=1);

namespace Domain\Subscription\Actions;

use Domain\Subscription\Data\SubscriptionData;
use Domain\Subscription\Models\Subscription;

class CreateSubscriptionAction
{
    public function execute(
        int $userId,
        int $planId,
        ?string $stripeSubscriptionId = null,
        string $status = 'active',
        ?string $currentPeriodEnd = null
    ): SubscriptionData {
        $subscription = Subscription::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'status' => $status,
            'current_period_end' => $currentPeriodEnd ? \Carbon\Carbon::parse($currentPeriodEnd) : null,
        ]);

        return SubscriptionData::fromModel($subscription->fresh());
    }
}
