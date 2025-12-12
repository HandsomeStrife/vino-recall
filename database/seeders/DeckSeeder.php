<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Admin\Models\Admin;
use Domain\Category\Models\Category;
use Domain\Deck\Actions\CreateDeckAction;
use Illuminate\Database\Seeder;

class DeckSeeder extends Seeder
{
    /**
     * Seed standalone decks (not in collections).
     */
    public function run(): void
    {
        $admin = Admin::first();
        $create_deck_action = new CreateDeckAction();

        // Get categories for assignment
        $categories = Category::all()->keyBy('name');

        $decks = [
            [
                'name' => 'French Wine Regions Deep Dive',
                'description' => 'Comprehensive study of French wine regions including Bordeaux, Burgundy, Champagne, Loire Valley, and Rhône Valley.',
                'categories' => ['Wine Regions'],
            ],
            [
                'name' => 'Italian Wine Essentials',
                'description' => 'Explore Italian wine regions, grape varieties, and classification systems (DOC, DOCG).',
                'categories' => ['Wine Regions', 'Label Terminology'],
            ],
            [
                'name' => 'Spanish Wine Journey',
                'description' => 'Discover Spanish wine regions including Rioja, Ribera del Duero, Priorat, and Sherry production.',
                'categories' => ['Wine Regions', 'Red Wines'],
            ],
            [
                'name' => 'New World Wine Regions',
                'description' => 'Study wine regions in California, Australia, New Zealand, Chile, Argentina, and South Africa.',
                'categories' => ['Wine Regions'],
            ],
            [
                'name' => 'Bordeaux Classification System',
                'description' => 'Master the 1855 Classification, Cru Bourgeois, and Right Bank classifications.',
                'categories' => ['Wine Regions', 'Label Terminology'],
            ],
            [
                'name' => 'Burgundy Grand Crus',
                'description' => 'Learn about Burgundy\'s Grand Cru vineyards and their distinctive characteristics.',
                'categories' => ['Wine Regions', 'White Wines', 'Red Wines'],
            ],
            [
                'name' => 'Champagne Production Methods',
                'description' => 'Detailed study of méthode champenoise, dosage levels, and vintage vs non-vintage Champagne.',
                'categories' => ['Sparkling Wines', 'Wine Production'],
            ],
            [
                'name' => 'Oak Aging and Wine',
                'description' => 'Understanding oak barrels, toasting levels, and their impact on wine flavor and structure.',
                'categories' => ['Wine Production'],
            ],
            [
                'name' => 'Wine Tasting Fundamentals',
                'description' => 'Learn the systematic approach to wine tasting using sight, smell, and taste.',
                'categories' => ['Wine Service'],
            ],
            [
                'name' => 'Wine and Cheese Pairing',
                'description' => 'Master the art of pairing wines with various cheese types and styles.',
                'categories' => ['Food & Wine Pairing'],
            ],
            [
                'name' => 'Organic and Biodynamic Wines',
                'description' => 'Explore organic viticulture, biodynamic principles, and natural winemaking.',
                'categories' => ['Wine Production'],
            ],
            [
                'name' => 'Climate Change and Wine',
                'description' => 'Understanding how climate change is affecting wine regions and grape growing.',
                'categories' => ['Wine Production', 'Wine Regions'],
            ],
            [
                'name' => 'Dessert Wine Styles',
                'description' => 'Study late harvest wines, ice wines, botrytis-affected wines, and fortified dessert wines.',
                'categories' => ['Wine Production', 'White Wines'],
            ],
            [
                'name' => 'Wine Faults and Flaws',
                'description' => 'Identify common wine faults including cork taint, oxidation, and volatile acidity.',
                'categories' => ['Wine Service'],
            ],
            [
                'name' => 'Wine Storage and Aging',
                'description' => 'Learn proper wine storage conditions, aging potential, and cellaring strategies.',
                'categories' => ['Wine Service'],
            ],
            [
                'name' => 'Rosé Wine Production',
                'description' => 'Explore different methods of rosé production including maceration and saignée.',
                'categories' => ['Rosé Wines', 'Wine Production'],
            ],
            [
                'name' => 'German Wine Classifications',
                'description' => 'Master the Prädikatswein system, VDP classifications, and German wine labels.',
                'categories' => ['Wine Regions', 'Label Terminology', 'White Wines'],
            ],
            [
                'name' => 'Portuguese Wine Discoveries',
                'description' => 'Explore Portuguese wine regions, Port wine styles, and indigenous grape varieties.',
                'categories' => ['Wine Regions', 'Red Wines'],
            ],
            [
                'name' => 'Wine Business and Marketing',
                'description' => 'Understanding wine distribution, pricing, and marketing strategies in the wine industry.',
                'categories' => ['Label Terminology'],
            ],
            [
                'name' => 'Advanced Food and Wine Pairing',
                'description' => 'Complex pairing principles considering texture, weight, and flavor intensity.',
                'categories' => ['Food & Wine Pairing'],
            ],
        ];

        foreach ($decks as $deck_data) {
            $deck = $create_deck_action->execute(
                name: $deck_data['name'],
                description: $deck_data['description'],
                is_active: true,
                created_by: $admin->id
            );

            // Attach categories using the Deck model
            $category_ids = [];
            foreach ($deck_data['categories'] as $category_name) {
                if ($categories->has($category_name)) {
                    $category_ids[] = $categories[$category_name]->id;
                }
            }
            if (! empty($category_ids)) {
                \Domain\Deck\Models\Deck::find($deck->id)->categories()->attach($category_ids);
            }
        }
    }
}

