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
        public ?string $shortcode = null,
        public ?int $cards_count = null,
        public ?array $category_ids = null,
        public ?Collection $categories = null,
    ) {}

    public static function fromModel(Deck $deck): self
    {
        $categories = null;
        $categoryIds = null;
        
        if ($deck->relationLoaded('categories')) {
            $categories = $deck->categories;
            $categoryIds = $deck->categories->pluck('id')->toArray();
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
            shortcode: $deck->pivot->shortcode ?? null,
            cards_count: $deck->cards_count ?? $deck->cards()->count(),
            category_ids: $categoryIds,
            categories: $categories,
        );
    }
}
