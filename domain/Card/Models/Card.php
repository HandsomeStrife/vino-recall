<?php

declare(strict_types=1);

namespace Domain\Card\Models;

use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\CardFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'deck_id',
        'card_type',
        'question',
        'answer',
        'image_path',
        'answer_choices',
        'correct_answer_indices',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CardReview::class);
    }
}
