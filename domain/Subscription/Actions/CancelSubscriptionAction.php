<?php

declare(strict_types=1);

namespace Domain\Subscription\Actions;

use Domain\Subscription\Actions\UpdateSubscriptionAction;

class CancelSubscriptionAction
{
    public function __construct(
        private UpdateSubscriptionAction $updateSubscriptionAction
    ) {
    }

    public function execute(int $subscriptionId): void
    {
        $this->updateSubscriptionAction->execute(
            subscriptionId: $subscriptionId,
            status: 'cancelled'
        );
    }
}

