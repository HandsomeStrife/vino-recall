<?php

declare(strict_types=1);

namespace Domain\Subscription\Data;

use Domain\Subscription\Models\Plan;
use Spatie\LaravelData\Data;

class PlanData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $stripe_price_id,
        public string $price,
        public ?string $features,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            name: $plan->name,
            stripe_price_id: $plan->stripe_price_id,
            price: (string) $plan->price,
            features: $plan->features,
            created_at: $plan->created_at->toDateTimeString(),
            updated_at: $plan->updated_at->toDateTimeString(),
        );
    }
}
