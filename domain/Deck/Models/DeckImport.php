<?php

declare(strict_types=1);

namespace Domain\Deck\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'deck_id',
        'filename',
        'format',
        'status',
        'imported_cards_count',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'format' => \Domain\Deck\Enums\ImportFormat::class,
            'status' => \Domain\Deck\Enums\ImportStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }
}

