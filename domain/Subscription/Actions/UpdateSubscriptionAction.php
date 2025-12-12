<?php

declare(strict_types=1);

namespace Domain\Subscription\Actions;

use Domain\Subscription\Data\SubscriptionData;
use Domain\Subscription\Models\Subscription;

class UpdateSubscriptionAction
{
    public function execute(
        int $subscriptionId,
        ?int $planId = null,
        ?string $stripeSubscriptionId = null,
        ?string $status = null,
        ?string $currentPeriodEnd = null
    ): SubscriptionData {
        $subscription = Subscription::findOrFail($subscriptionId);

        $updateData = [];

        if ($planId !== null) {
            $updateData['plan_id'] = $planId;
        }

        if ($stripeSubscriptionId !== null) {
            $updateData['stripe_subscription_id'] = $stripeSubscriptionId;
        }

        if ($status !== null) {
            $updateData['status'] = $status;
        }

        if ($currentPeriodEnd !== null) {
            $updateData['current_period_end'] = \Carbon\Carbon::parse($currentPeriodEnd);
        }

        $subscription->update($updateData);

        return SubscriptionData::fromModel($subscription->fresh());
    }
}
