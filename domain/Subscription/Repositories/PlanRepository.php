<?php

declare(strict_types=1);

namespace Domain\Subscription\Repositories;

use Domain\Subscription\Data\PlanData;
use Domain\Subscription\Models\Plan;

class PlanRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, PlanData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Plan::all()->map(fn (Plan $plan) => PlanData::fromModel($plan));
    }

    public function findById(int $id): ?PlanData
    {
        $plan = Plan::find($id);

        if ($plan === null) {
            return null;
        }

        return PlanData::fromModel($plan);
    }
}
