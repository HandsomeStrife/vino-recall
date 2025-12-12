<?php

declare(strict_types=1);

namespace Domain\Category\Actions;

use Domain\Category\Models\Category;

class DeleteCategoryAction
{
    public function execute(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        // Detach all deck relationships before deletion
        $category->decks()->detach();

        $category->delete();
    }
}
