<?php

declare(strict_types=1);

namespace Domain\Material\Actions;

use Domain\Material\Models\Material;
use Illuminate\Support\Facades\Storage;

class DeleteMaterialAction
{
    public function execute(int $material_id): void
    {
        $material = Material::findOrFail($material_id);

        // Delete associated image if it exists
        if ($material->image_path) {
            Storage::disk('public')->delete($material->image_path);
        }

        $material->delete();

        // Re-order remaining materials
        $remaining_materials = Material::where('deck_id', $material->deck_id)
            ->orderBy('sort_order')
            ->get();

        foreach ($remaining_materials as $index => $remaining_material) {
            $remaining_material->update(['sort_order' => $index]);
        }
    }
}
