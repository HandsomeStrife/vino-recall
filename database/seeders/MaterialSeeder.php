<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Deck\Models\Deck;
use Domain\Material\Actions\CreateMaterialAction;
use Domain\Material\Enums\ImagePosition;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Seed learning materials for all decks.
     */
    public function run(): void
    {
        $create_material_action = new CreateMaterialAction();
        $decks = Deck::all();

        foreach ($decks as $deck) {
            // Skip collections (they don't have materials directly)
            if ($deck->is_collection) {
                continue;
            }

            // Generate 3-5 materials per deck
            $material_count = rand(3, 5);
            $image_positions = [ImagePosition::TOP, ImagePosition::LEFT, ImagePosition::RIGHT, ImagePosition::BOTTOM];

            for ($i = 0; $i < $material_count; $i++) {
                $material_data = $this->generateMaterialContent($deck->name, $i);
                $image_position = $image_positions[$i % count($image_positions)];

                $create_material_action->execute(
                    deck_id: $deck->id,
                    content: $material_data['content'],
                    title: $material_data['title'],
                    image_path: null,
                    image_position: $image_position
                );
            }
        }
    }

    /**
     * Generate material content based on deck name and index.
     */
    private function generateMaterialContent(string $deck_name, int $index): array
    {
        $materials = [
            [
                'title' => 'Introduction and Overview',
                'content' => '<h2>Welcome to Wine Education</h2><p>Wine is one of the oldest and most complex beverages known to humanity. This course will guide you through understanding wine from grape to glass.</p><p><strong>What You Will Learn:</strong></p><ul><li>The fundamentals of wine production</li><li>Key grape varieties and their characteristics</li><li>How to taste and evaluate wine properly</li><li>Major wine regions of the world</li></ul><p>Take your time with each section and remember: wine education is a journey, not a destination.</p>',
            ],
            [
                'title' => 'Key Concepts and Terminology',
                'content' => '<h2>Essential Wine Vocabulary</h2><p>Understanding wine requires familiarity with specific terminology. Here are the most important concepts:</p><p><strong>Acidity:</strong> The tartness or crispness in wine, essential for freshness and aging potential.</p><p><strong>Tannin:</strong> Compounds from grape skins that create a drying sensation in the mouth, important in red wines.</p><p><strong>Body:</strong> The weight and fullness of wine in your mouth, ranging from light to full-bodied.</p><p><strong>Terroir:</strong> The complete natural environment where grapes are grown, including soil, climate, and topography.</p>',
            ],
            [
                'title' => 'Historical Context',
                'content' => '<h2>The History of Wine</h2><p>Wine production dates back thousands of years, with evidence of winemaking found in ancient civilizations throughout the Mediterranean.</p><p><strong>Ancient Origins:</strong> Archaeological evidence suggests wine production began in the Caucasus region around 6000 BCE.</p><p><strong>European Expansion:</strong> The Romans spread viticulture throughout their empire, establishing many famous wine regions that exist today.</p><p><strong>New World Development:</strong> European colonization brought winemaking to the Americas, Australia, and South Africa in the 16th-19th centuries.</p><p><strong>Modern Era:</strong> Today, wine is produced on every continent except Antarctica, with both traditional and innovative approaches.</p>',
            ],
            [
                'title' => 'Study Tips and Best Practices',
                'content' => '<h2>How to Study Effectively</h2><p>Learning about wine requires both theoretical knowledge and practical experience. Here are strategies for success:</p><ol><li><strong>Regular Review:</strong> Use the spaced repetition system to review cards at optimal intervals</li><li><strong>Active Tasting:</strong> Whenever possible, taste wines while studying to connect theory with experience</li><li><strong>Take Notes:</strong> Keep a wine journal to record your observations and impressions</li><li><strong>Study in Context:</strong> Group related topics together, such as studying a region alongside its grape varieties</li><li><strong>Practice Consistently:</strong> Daily short sessions are more effective than occasional long study marathons</li></ol>',
            ],
            [
                'title' => 'Common Mistakes to Avoid',
                'content' => '<h2>Pitfalls in Wine Learning</h2><p>As you progress in your wine education, be aware of these common misconceptions and mistakes:</p><p><strong>Overthinking Terminology:</strong> While vocabulary is important, don\'t get lost in jargon. Focus on understanding concepts first.</p><p><strong>Price Equals Quality:</strong> Expensive wines aren\'t always better. Learn to evaluate wine on its own merits.</p><p><strong>Ignoring Personal Preference:</strong> There are no "wrong" wines to enjoy. Develop your palate based on what you genuinely like.</p><p><strong>Rushing Through Regions:</strong> Each wine region has unique characteristics. Take time to understand what makes each special.</p><p><strong>Neglecting Food Pairing:</strong> Wine and food are meant to complement each other. Study pairing principles alongside wine knowledge.</p>',
            ],
        ];

        // Select material based on index, cycling through the pool
        $material = $materials[$index % count($materials)];

        return $material;
    }
}

