<?php

declare(strict_types=1);

namespace Domain\Deck\Data;

use Domain\Deck\Models\Deck;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DeckData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $image_path,
        public bool $is_active,
        public ?int $created_by,
        public string $created_at,
        public string $updated_at,
        public ?string $identifier = null,
        public ?string $shortcode = null,
        public ?int $cards_count = null,
        public ?array $category_ids = null,
        public ?Collection $categories = null,
        public ?int $parent_deck_id = null,
        public ?Collection $children = null,
        public bool $is_collection = false,
        public string $deck_type = 'standalone',
        public ?string $parent_name = null,
    ) {}

    public static function fromModel(Deck $deck): self
    {
        $categories = null;
        $categoryIds = null;

        if ($deck->relationLoaded('categories')) {
            $categories = $deck->categories;
            $categoryIds = $deck->categories->pluck('id')->toArray();
        }

        $children = null;
        if ($deck->relationLoaded('children')) {
            $children = $deck->children->map(fn (Deck $child) => self::fromModel($child));
        }

        $parentName = null;
        if ($deck->relationLoaded('parent') && $deck->parent !== null) {
            $parentName = $deck->parent->name;
        }

        return new self(
            id: $deck->id,
            name: $deck->name,
            description: $deck->description,
            image_path: $deck->image_path ?? null,
            is_active: $deck->is_active,
            created_by: $deck->created_by,
            created_at: $deck->created_at->toDateTimeString(),
            updated_at: $deck->updated_at->toDateTimeString(),
            identifier: $deck->identifier ?? null,
            shortcode: $deck->pivot->shortcode ?? null,
            cards_count: $deck->cards_count ?? $deck->cards()->count(),
            category_ids: $categoryIds,
            categories: $categories,
            parent_deck_id: $deck->parent_deck_id,
            children: $children,
            is_collection: $deck->is_collection,
            deck_type: $deck->getDeckType(),
            parent_name: $parentName,
        );
    }
}
