<?php

declare(strict_types=1);

namespace Domain\Material\Actions;

use Domain\Material\Enums\ImagePosition;
use Domain\Material\Models\Material;

class UpdateMaterialAction
{
    public function execute(
        int $material_id,
        string $content,
        ?string $title = null,
        ?string $image_path = null,
        ImagePosition $image_position = ImagePosition::TOP,
    ): Material {
        $material = Material::findOrFail($material_id);

        $material->update([
            'title' => $title,
            'content' => $content,
            'image_path' => $image_path,
            'image_position' => $image_position->value,
        ]);

        return $material->fresh();
    }
}

