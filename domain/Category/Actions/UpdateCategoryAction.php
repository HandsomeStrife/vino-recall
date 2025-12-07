<?php

declare(strict_types=1);

namespace Domain\Category\Actions;

use Domain\Category\Data\CategoryData;
use Domain\Category\Models\Category;

class UpdateCategoryAction
{
    public function execute(
        int $categoryId,
        string $name,
        ?string $description,
        bool $is_active,
        ?string $image_path = null
    ): CategoryData {
        $category = Category::findOrFail($categoryId);

        $category->update([
            'name' => $name,
            'description' => $description,
            'is_active' => $is_active,
            'image_path' => $image_path,
        ]);

        return CategoryData::from($category->fresh());
    }
}

