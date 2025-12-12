<?php

declare(strict_types=1);

namespace Domain\Subscription\Data;

use Domain\Subscription\Models\Subscription;
use Spatie\LaravelData\Data;

class SubscriptionData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $plan_id,
        public ?string $stripe_subscription_id,
        public string $status,
        public ?\Carbon\Carbon $current_period_end,
        public string $created_at,
        public string $updated_at,
        public ?PlanData $plan = null,
    ) {}

    public static function fromModel(Subscription $subscription): self
    {
        return new self(
            id: $subscription->id,
            user_id: $subscription->user_id,
            plan_id: $subscription->plan_id,
            stripe_subscription_id: $subscription->stripe_subscription_id,
            status: $subscription->status,
            current_period_end: $subscription->current_period_end,
            created_at: $subscription->created_at->toDateTimeString(),
            updated_at: $subscription->updated_at->toDateTimeString(),
            plan: $subscription->plan ? PlanData::fromModel($subscription->plan) : null,
        );
    }
}
