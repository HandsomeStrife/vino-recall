<?php

declare(strict_types=1);

namespace Domain\Category\Models;

use Domain\Admin\Models\Admin;
use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\CategoryFactory::new();
    }

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function decks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'category_deck')
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
