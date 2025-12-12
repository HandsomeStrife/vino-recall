<?php

declare(strict_types=1);

namespace Domain\Material\Repositories;

use Domain\Material\Data\MaterialData;
use Domain\Material\Models\Material;
use Illuminate\Support\Collection;

class MaterialRepository
{
    /**
     * Get all materials for a deck, ordered by sort_order.
     *
     * @return Collection<MaterialData>
     */
    public function getByDeckId(int $deck_id): Collection
    {
        return Material::where('deck_id', $deck_id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($material) => MaterialData::fromModel($material));
    }

    /**
     * Find a material by ID.
     */
    public function findById(int $id): ?MaterialData
    {
        $material = Material::find($id);

        return $material ? MaterialData::fromModel($material) : null;
    }

    /**
     * Get the count of materials for a deck.
     */
    public function countByDeckId(int $deck_id): int
    {
        return Material::where('deck_id', $deck_id)->count();
    }

    /**
     * Check if a deck has any materials.
     */
    public function hasMaterials(int $deck_id): bool
    {
        return Material::where('deck_id', $deck_id)->exists();
    }
}

