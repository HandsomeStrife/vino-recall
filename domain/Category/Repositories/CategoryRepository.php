<?php

declare(strict_types=1);

namespace Domain\Category\Repositories;

use Domain\Category\Data\CategoryData;
use Domain\Category\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository
{
    /**
     * @return Collection<CategoryData>
     */
    public function getAll(): Collection
    {
        return Category::withCount('decks')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => CategoryData::from($category));
    }

    public function findById(int $id): ?CategoryData
    {
        $category = Category::withCount('decks')->find($id);

        return $category ? CategoryData::from($category) : null;
    }

    /**
     * @return Collection<CategoryData>
     */
    public function getActiveCategories(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => CategoryData::from($category));
    }

    /**
     * @return Collection<CategoryData>
     */
    public function getCategoriesWithDeckCount(): Collection
    {
        return Category::withCount('decks')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => CategoryData::from($category));
    }
}
