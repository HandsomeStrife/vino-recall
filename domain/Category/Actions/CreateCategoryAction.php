<?php

declare(strict_types=1);

namespace Domain\Category\Actions;

use Domain\Category\Data\CategoryData;
use Domain\Category\Models\Category;

class CreateCategoryAction
{
    public function execute(
        string $name,
        ?string $description,
        bool $is_active,
        int $created_by,
        ?string $image_path = null
    ): CategoryData {
        $category = Category::create([
            'name' => $name,
            'description' => $description,
            'is_active' => $is_active,
            'created_by' => $created_by,
            'image_path' => $image_path,
        ]);

        return CategoryData::from($category);
    }
}

