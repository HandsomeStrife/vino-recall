<?php

declare(strict_types=1);

namespace Domain\Deck\Models;

use Domain\Card\Models\Card;
use Domain\Category\Models\Category;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\DeckFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'is_active',
        'is_collection',
        'created_by',
        'parent_deck_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_collection' => 'boolean',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'deck_user')
            ->withPivot('enrolled_at', 'shortcode')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_deck')
            ->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'parent_deck_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Deck::class, 'parent_deck_id');
    }

    /**
     * Check if this deck is a collection (explicitly marked as parent container).
     */
    public function isCollection(): bool
    {
        return $this->is_collection;
    }

    /**
     * Check if this deck is a child deck (has a parent).
     */
    public function isChild(): bool
    {
        return $this->parent_deck_id !== null;
    }

    /**
     * Check if this deck is standalone (not a collection and no parent).
     */
    public function isStandalone(): bool
    {
        return !$this->is_collection && !$this->isChild();
    }

    /**
     * Get the deck type as a string.
     */
    public function getDeckType(): string
    {
        if ($this->isChild()) {
            return 'child';
        }
        if ($this->is_collection) {
            return 'collection';
        }
        return 'standalone';
    }
}
