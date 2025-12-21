<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Admin\Models\Admin;
use Domain\Category\Models\Category;
use Domain\Deck\Actions\CreateDeckAction;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Seed deck collections and their child decks.
     */
    public function run(): void
    {
        $admin = Admin::first();
        $create_deck_action = new CreateDeckAction;

        // Get categories for assignment
        $red_wines = Category::where('name', 'Red Wines')->first();
        $white_wines = Category::where('name', 'White Wines')->first();
        $sparkling_wines = Category::where('name', 'Sparkling Wines')->first();
        $wine_regions = Category::where('name', 'Wine Regions')->first();
        $wine_production = Category::where('name', 'Wine Production')->first();
        $wine_service = Category::where('name', 'Wine Service')->first();
        $grape_varieties = Category::where('name', 'Grape Varieties')->first();
        $label_terminology = Category::where('name', 'Label Terminology')->first();

        // Collection 1: WSET Level 1
        $wset_level_1 = $create_deck_action->execute(
            name: 'WSET Level 1 Collection',
            description: 'The WSET Level 1 Award in Wines is an introductory qualification designed for individuals with little or no prior knowledge of wine, providing a foundational understanding of the basics of wine, viticulture, and winemaking.',
            is_active: true,
            created_by: $admin->id,
            is_collection: true
        );

        // Child decks for WSET Level 1
        $wset1_deck1 = $create_deck_action->execute(
            name: 'WSET Level 1: What is Wine?',
            description: 'Introduction to wine types (still, sparkling, fortified), style dimensions (color, sweetness, acidity, tannin), and common aroma and flavor categories.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_1->id
        );
        \Domain\Deck\Models\Deck::find($wset1_deck1->id)->categories()->attach([$grape_varieties->id, $wine_production->id]);

        $wset1_deck2 = $create_deck_action->execute(
            name: 'WSET Level 1: Wine Service',
            description: 'Master wine service basics including serving temperatures, glassware selection, and decanting techniques.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_1->id
        );
        \Domain\Deck\Models\Deck::find($wset1_deck2->id)->categories()->attach([$wine_service->id]);

        $wset1_deck3 = $create_deck_action->execute(
            name: 'WSET Level 1: Food & Wine Pairing',
            description: 'Learn basic principles of matching food and wine for optimal enjoyment.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_1->id
        );

        // Collection 2: WSET Level 2
        $wset_level_2 = $create_deck_action->execute(
            name: 'WSET Level 2 Collection',
            description: 'The WSET Level 2 Award in Wines is an intermediate-level qualification for those seeking a deeper understanding of wines of the world. Perfect for wine professionals and enthusiasts.',
            is_active: true,
            created_by: $admin->id,
            is_collection: true
        );

        // Child decks for WSET Level 2
        $wset2_deck1 = $create_deck_action->execute(
            name: 'WSET Level 2: Wine Regions',
            description: 'Explore major wine regions of the world including Bordeaux, Burgundy, Rioja, Tuscany, and Napa Valley.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_2->id
        );
        \Domain\Deck\Models\Deck::find($wset2_deck1->id)->categories()->attach([$wine_regions->id]);

        $wset2_deck2 = $create_deck_action->execute(
            name: 'WSET Level 2: Label Terminology',
            description: 'Understand wine label terms including Grand Cru, Reserva, DOC, and vintage classifications.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_2->id
        );
        \Domain\Deck\Models\Deck::find($wset2_deck2->id)->categories()->attach([$label_terminology->id]);

        $wset2_deck3 = $create_deck_action->execute(
            name: 'WSET Level 2: Wine Production',
            description: 'Learn winemaking processes including fermentation methods, oak aging, and malolactic fermentation.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wset_level_2->id
        );
        \Domain\Deck\Models\Deck::find($wset2_deck3->id)->categories()->attach([$wine_production->id]);

        // Collection 3: Wine Fundamentals
        $wine_fundamentals = $create_deck_action->execute(
            name: 'Wine Fundamentals Collection',
            description: 'Essential knowledge for wine enthusiasts covering grape varieties, tasting techniques, and storage.',
            is_active: true,
            created_by: $admin->id,
            is_collection: true
        );

        // Child decks for Wine Fundamentals
        $fund_deck1 = $create_deck_action->execute(
            name: 'Red Grape Varieties',
            description: 'Study major red grape varieties including Cabernet Sauvignon, Merlot, Pinot Noir, and Syrah.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wine_fundamentals->id
        );
        \Domain\Deck\Models\Deck::find($fund_deck1->id)->categories()->attach([$red_wines->id, $grape_varieties->id]);

        $fund_deck2 = $create_deck_action->execute(
            name: 'White Grape Varieties',
            description: 'Explore white grape varieties like Chardonnay, Sauvignon Blanc, Riesling, and Pinot Grigio.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $wine_fundamentals->id
        );
        \Domain\Deck\Models\Deck::find($fund_deck2->id)->categories()->attach([$white_wines->id, $grape_varieties->id]);

        // Collection 4: Advanced Wine Topics
        $advanced_topics = $create_deck_action->execute(
            name: 'Advanced Wine Topics Collection',
            description: 'Deep dive into specialized wine knowledge including sparkling wine production and fortified wines.',
            is_active: true,
            created_by: $admin->id,
            is_collection: true
        );

        // Child decks for Advanced Topics
        $adv_deck1 = $create_deck_action->execute(
            name: 'Sparkling Wine Production Methods',
            description: 'Learn the traditional method (Champagne), Charmat method (Prosecco), and other sparkling wine production techniques.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $advanced_topics->id
        );
        \Domain\Deck\Models\Deck::find($adv_deck1->id)->categories()->attach([$sparkling_wines->id, $wine_production->id]);

        $adv_deck2 = $create_deck_action->execute(
            name: 'Fortified Wines',
            description: 'Explore Port, Sherry, Madeira, and other fortified wine styles.',
            is_active: true,
            created_by: $admin->id,
            parent_deck_id: $advanced_topics->id
        );
        \Domain\Deck\Models\Deck::find($adv_deck2->id)->categories()->attach([$wine_production->id]);
    }
}
