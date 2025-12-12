<?php

declare(strict_types=1);

namespace Domain\Material\Actions;

use Domain\Material\Enums\ImagePosition;
use Domain\Material\Models\Material;

class CreateMaterialAction
{
    public function execute(
        int $deck_id,
        string $content,
        ?string $title = null,
        ?string $image_path = null,
        ImagePosition $image_position = ImagePosition::TOP,
    ): Material {
        // Get the next sort order
        $max_sort_order = Material::where('deck_id', $deck_id)
            ->max('sort_order');

        $sort_order = $max_sort_order !== null ? $max_sort_order + 1 : 0;

        return Material::create([
            'deck_id' => $deck_id,
            'title' => $title,
            'content' => $content,
            'image_path' => $image_path,
            'image_position' => $image_position->value,
            'sort_order' => $sort_order,
        ]);
    }
}

