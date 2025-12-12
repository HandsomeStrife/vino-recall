<?php

declare(strict_types=1);

namespace Domain\Material\Data;

use Domain\Material\Enums\ImagePosition;
use Domain\Material\Models\Material;
use Spatie\LaravelData\Data;

class MaterialData extends Data
{
    public function __construct(
        public int $id,
        public int $deck_id,
        public ?string $title,
        public string $content,
        public ?string $image_path,
        public ImagePosition $image_position,
        public int $sort_order,
    ) {}

    public static function fromModel(Material $material): self
    {
        return new self(
            id: $material->id,
            deck_id: $material->deck_id,
            title: $material->title,
            content: $material->content,
            image_path: $material->image_path,
            image_position: ImagePosition::from($material->image_position),
            sort_order: $material->sort_order,
        );
    }
}

