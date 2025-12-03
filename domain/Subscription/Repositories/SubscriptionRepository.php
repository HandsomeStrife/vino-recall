<?php

declare(strict_types=1);

namespace Domain\Subscription\Repositories;

use Domain\Subscription\Data\SubscriptionData;
use Domain\Subscription\Models\Subscription;

class SubscriptionRepository
{
    public function findByUserId(int $userId): ?SubscriptionData
    {
        $subscription = Subscription::where('user_id', $userId)->first();

        if ($subscription === null) {
            return null;
        }

        return SubscriptionData::fromModel($subscription);
    }

    public function findById(int $id): ?SubscriptionData
    {
        $subscription = Subscription::find($id);

        if ($subscription === null) {
            return null;
        }

        return SubscriptionData::fromModel($subscription);
    }

    /**
     * @return \Illuminate\Support\Collection<int, SubscriptionData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Subscription::all()->map(fn (Subscription $subscription) => SubscriptionData::fromModel($subscription));
    }
}
