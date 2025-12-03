<?php

declare(strict_types=1);

namespace Domain\Card\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardReview extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\CardReviewFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'card_id',
        'rating',
        'is_correct',
        'is_practice',
        'selected_answer',
        'ease_factor',
        'next_review_at',
    ];

    protected function casts(): array
    {
        return [
            'next_review_at' => 'datetime',
            'ease_factor' => 'decimal:2',
            'is_correct' => 'boolean',
            'is_practice' => 'boolean',
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
