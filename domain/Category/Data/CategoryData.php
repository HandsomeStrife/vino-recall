<?php

declare(strict_types=1);

namespace Domain\Category\Data;

use Domain\Category\Models\Category;
use Spatie\LaravelData\Data;

class CategoryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $image_path,
        public bool $is_active,
        public int $created_by,
        public string $created_at,
        public string $updated_at,
        public ?int $decks_count = null,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            image_path: $category->image_path,
            is_active: $category->is_active,
            created_by: $category->created_by,
            created_at: $category->created_at->toDateTimeString(),
            updated_at: $category->updated_at->toDateTimeString(),
            decks_count: $category->decks_count ?? null,
        );
    }
}
