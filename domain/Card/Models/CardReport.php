<?php

declare(strict_types=1);

namespace Domain\Card\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardReport extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'card_shortcode',
        'message',
        'status',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
