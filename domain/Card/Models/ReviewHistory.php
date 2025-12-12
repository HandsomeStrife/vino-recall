<?php

declare(strict_types=1);

namespace Domain\Card\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log of all review events for accuracy calculation and analytics.
 * Multiple records per user-card pair (one per review event).
 */
class ReviewHistory extends Model
{
    use HasFactory;

    protected $table = 'review_history';

    protected static function newFactory()
    {
        return \Database\Factories\ReviewHistoryFactory::new();
    }

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'card_id',
        'is_correct',
        'previous_stage',
        'new_stage',
        'is_practice',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'previous_stage' => 'integer',
            'new_stage' => 'integer',
            'is_practice' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
