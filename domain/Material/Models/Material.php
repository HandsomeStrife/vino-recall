<?php

declare(strict_types=1);

namespace Domain\Material\Models;

use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\MaterialFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'deck_id',
        'title',
        'content',
        'image_path',
        'image_position',
        'sort_order',
    ];

    protected $table = 'deck_materials';

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }
}

