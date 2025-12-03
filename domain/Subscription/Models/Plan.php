<?php

declare(strict_types=1);

namespace Domain\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Database\Factories\PlanFactory factory($count = null, $state = [])
 */
class Plan extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\PlanFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'stripe_price_id',
        'price',
        'billing_period',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
