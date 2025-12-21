<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Admin\Models\Admin;
use Domain\Category\Actions\CreateCategoryAction;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed wine categories.
     */
    public function run(): void
    {
        $admin = Admin::first();
        $create_category_action = new CreateCategoryAction;

        $categories = [
            [
                'name' => 'Red Wines',
                'description' => 'Learn about red wine varieties, production methods, and tasting characteristics.',
            ],
            [
                'name' => 'White Wines',
                'description' => 'Explore white wine grapes, regions, and serving recommendations.',
            ],
            [
                'name' => 'Sparkling Wines',
                'description' => 'Discover sparkling wine production methods, from Champagne to Prosecco.',
            ],
            [
                'name' => 'Rosé Wines',
                'description' => 'Understanding rosé wine styles, production, and food pairings.',
            ],
            [
                'name' => 'Wine Regions',
                'description' => 'Study major wine regions around the world and their signature wines.',
            ],
            [
                'name' => 'Wine Production',
                'description' => 'Learn winemaking processes from vineyard to bottle.',
            ],
            [
                'name' => 'Wine Service',
                'description' => 'Master wine service techniques, temperatures, and glassware.',
            ],
            [
                'name' => 'Food & Wine Pairing',
                'description' => 'Principles of matching food and wine for optimal enjoyment.',
            ],
            [
                'name' => 'Grape Varieties',
                'description' => 'Deep dive into major grape varieties and their characteristics.',
            ],
            [
                'name' => 'Label Terminology',
                'description' => 'Decode wine labels and understand classification systems.',
            ],
        ];

        foreach ($categories as $category) {
            $create_category_action->execute(
                name: $category['name'],
                description: $category['description'],
                is_active: true,
                created_by: $admin->id
            );
        }
    }
}
