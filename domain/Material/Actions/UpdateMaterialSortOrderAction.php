<?php

declare(strict_types=1);

namespace Domain\Material\Actions;

use Domain\Material\Models\Material;

class UpdateMaterialSortOrderAction
{
    /**
     * Update the sort order of materials based on an array of IDs.
     *
     * @param  array<int>  $ordered_ids  Array of material IDs in the desired order
     */
    public function execute(int $deck_id, array $ordered_ids): void
    {
        foreach ($ordered_ids as $index => $material_id) {
            Material::where('id', $material_id)
                ->where('deck_id', $deck_id)
                ->update(['sort_order' => $index]);
        }
    }
}
