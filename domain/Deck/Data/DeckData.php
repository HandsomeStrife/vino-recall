<?php

declare(strict_types=1);

namespace Domain\Deck\Data;

use Domain\Deck\Models\Deck;
use Spatie\LaravelData\Data;

class DeckData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $category,
        public ?string $image_path,
        public bool $is_active,
        public ?int $created_by,
        public string $created_at,
        public string $updated_at,
        public ?string $shortcode = null,
    ) {}

    public static function fromModel(Deck $deck): self
    {
        return new self(
            id: $deck->id,
            name: $deck->name,
            description: $deck->description,
            category: $deck->category ?? null,
            image_path: $deck->image_path ?? null,
            is_active: $deck->is_active,
            created_by: $deck->created_by,
            created_at: $deck->created_at->toDateTimeString(),
            updated_at: $deck->updated_at->toDateTimeString(),
            shortcode: $deck->pivot->shortcode ?? null,
        );
    }
}
