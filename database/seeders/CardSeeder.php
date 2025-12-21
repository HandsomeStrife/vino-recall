<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Enums\CardType;
use Domain\Deck\Models\Deck;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    /**
     * Seed flashcards for all decks.
     */
    public function run(): void
    {
        $create_card_action = new CreateCardAction;
        $decks = Deck::all();

        foreach ($decks as $deck) {
            // Skip collections (they don't have cards directly)
            if ($deck->is_collection) {
                continue;
            }

            // Generate 20-30 cards per deck
            $card_count = rand(20, 30);

            for ($i = 0; $i < $card_count; $i++) {
                $card_data = $this->generateCardData($deck->name, $i);

                $create_card_action->execute(
                    deckId: $deck->id,
                    question: $card_data['question'],
                    answer: $card_data['answer'],
                    cardType: CardType::MULTIPLE_CHOICE,
                    answerChoices: $card_data['choices'],
                    correctAnswerIndices: $card_data['correct_indices']
                );
            }
        }
    }

    /**
     * Generate card data based on deck name and index.
     */
    private function generateCardData(string $deck_name, int $index): array
    {
        // Wine-related questions pool
        $questions = [
            // Grape Varieties
            ['q' => 'Which grape is known for producing light-bodied red wines with high acidity?', 'a' => 'Pinot Noir', 'c' => ['Cabernet Sauvignon', 'Pinot Noir', 'Syrah', 'Malbec'], 'i' => [1]],
            ['q' => 'What is the primary grape in Barolo and Barbaresco wines?', 'a' => 'Nebbiolo', 'c' => ['Sangiovese', 'Nebbiolo', 'Barbera', 'Dolcetto'], 'i' => [1]],
            ['q' => 'Which white grape is known for high acidity and citrus flavors?', 'a' => 'Sauvignon Blanc', 'c' => ['Chardonnay', 'Viognier', 'Sauvignon Blanc', 'Marsanne'], 'i' => [2]],
            ['q' => 'What grape variety is Rioja primarily made from?', 'a' => 'Tempranillo', 'c' => ['Garnacha', 'Tempranillo', 'Monastrell', 'Graciano'], 'i' => [1]],
            ['q' => 'Which grape is known for producing full-bodied wines with notes of blackcurrant?', 'a' => 'Cabernet Sauvignon', 'c' => ['Cabernet Sauvignon', 'Merlot', 'Pinot Noir', 'Gamay'], 'i' => [0]],

            // Wine Regions
            ['q' => 'Which region is famous for Pinot Noir and Chardonnay?', 'a' => 'Burgundy', 'c' => ['Bordeaux', 'Burgundy', 'Champagne', 'Loire Valley'], 'i' => [1]],
            ['q' => 'What is the primary wine region in Argentina?', 'a' => 'Mendoza', 'c' => ['Patagonia', 'Mendoza', 'Salta', 'Cafayate'], 'i' => [1]],
            ['q' => 'Which New Zealand region is renowned for Sauvignon Blanc?', 'a' => 'Marlborough', 'c' => ['Central Otago', 'Hawke\'s Bay', 'Marlborough', 'Waipara'], 'i' => [2]],
            ['q' => 'Where is the Douro Valley located?', 'a' => 'Portugal', 'c' => ['Spain', 'Italy', 'Portugal', 'France'], 'i' => [2]],
            ['q' => 'Which Italian region is famous for Chianti?', 'a' => 'Tuscany', 'c' => ['Piedmont', 'Veneto', 'Tuscany', 'Sicily'], 'i' => [2]],

            // Wine Production
            ['q' => 'What is malolactic fermentation?', 'a' => 'Converting malic acid to lactic acid', 'c' => ['Converting sugar to alcohol', 'Converting malic acid to lactic acid', 'Removing tannins', 'Adding sulfites'], 'i' => [1]],
            ['q' => 'What does "sur lie" aging mean?', 'a' => 'Aging on the lees', 'c' => ['Aging in oak', 'Aging on the lees', 'Aging in bottle', 'Aging in stainless steel'], 'i' => [1]],
            ['q' => 'Which method is used for traditional Champagne production?', 'a' => 'Méthode Champenoise', 'c' => ['Charmat Method', 'Méthode Champenoise', 'Transfer Method', 'Carbonation'], 'i' => [1]],
            ['q' => 'What is the primary difference between red and white wine production?', 'a' => 'Red wine ferments with skins', 'c' => ['Red wine uses red grapes', 'Red wine ferments with skins', 'Red wine ferments longer', 'Red wine has higher alcohol'], 'i' => [1]],
            ['q' => 'What does cold stabilization do?', 'a' => 'Removes tartrate crystals', 'c' => ['Increases acidity', 'Removes tartrate crystals', 'Adds tannins', 'Speeds fermentation'], 'i' => [1]],

            // Wine Service
            ['q' => 'What is the ideal serving temperature for full-bodied red wines?', 'a' => '15-18°C (59-64°F)', 'c' => ['5-10°C (41-50°F)', '10-13°C (50-55°F)', '15-18°C (59-64°F)', '20-25°C (68-77°F)'], 'i' => [2]],
            ['q' => 'What does "Brut" mean on a sparkling wine label?', 'a' => 'Dry', 'c' => ['Sweet', 'Semi-sweet', 'Dry', 'Very sweet'], 'i' => [2]],
            ['q' => 'Why should wine be decanted?', 'a' => 'To separate sediment and aerate', 'c' => ['To chill faster', 'To separate sediment and aerate', 'To add bubbles', 'To reduce alcohol'], 'i' => [1]],
            ['q' => 'What is the ideal serving temperature for sparkling wines?', 'a' => '6-10°C (43-50°F)', 'c' => ['6-10°C (43-50°F)', '10-13°C (50-55°F)', '13-16°C (55-61°F)', '16-18°C (61-64°F)'], 'i' => [0]],
            ['q' => 'Which glass shape is best for full-bodied red wines?', 'a' => 'Large bowl-shaped glass', 'c' => ['Tall flute', 'Small narrow glass', 'Large bowl-shaped glass', 'Wide shallow glass'], 'i' => [2]],

            // Label Terminology
            ['q' => 'What does "Grand Cru" mean in Burgundy?', 'a' => 'Highest vineyard classification', 'c' => ['A grape variety', 'Highest vineyard classification', 'A winemaking technique', 'Type of barrel'], 'i' => [1]],
            ['q' => 'What does "Reserva" indicate on a Spanish wine label?', 'a' => 'Wine has been aged', 'c' => ['Sweet wine', 'Wine has been aged', 'Organic grapes', 'Sparkling wine'], 'i' => [1]],
            ['q' => 'What does "Vintage" mean on a wine label?', 'a' => 'Year grapes were harvested', 'c' => ['Year bottled', 'Year grapes were harvested', 'Age of vines', 'Winery founding year'], 'i' => [1]],
            ['q' => 'What does "DOC" stand for on an Italian wine label?', 'a' => 'Denominazione di Origine Controllata', 'c' => ['Denominazione di Origine Controllata', 'Di Origine Certificata', 'Denominazione Organica', 'Di Origine Classica'], 'i' => [0]],
            ['q' => 'What does "Cru Classé" mean in Bordeaux?', 'a' => 'Classified growth', 'c' => ['Classified growth', 'Single vineyard', 'Old vines', 'Estate bottled'], 'i' => [0]],

            // Food Pairing
            ['q' => 'What wine pairs best with oysters?', 'a' => 'Muscadet', 'c' => ['Cabernet Sauvignon', 'Muscadet', 'Malbec', 'Zinfandel'], 'i' => [1]],
            ['q' => 'Which wine characteristic balances salty foods?', 'a' => 'Acidity', 'c' => ['Tannin', 'Acidity', 'Sweetness', 'Alcohol'], 'i' => [1]],
            ['q' => 'What wine style pairs well with spicy Asian cuisine?', 'a' => 'Off-dry Riesling', 'c' => ['Dry Cabernet', 'Off-dry Riesling', 'Oaked Chardonnay', 'Full-bodied red'], 'i' => [1]],
            ['q' => 'Which wine pairs best with grilled steak?', 'a' => 'Cabernet Sauvignon', 'c' => ['Pinot Grigio', 'Moscato', 'Cabernet Sauvignon', 'Riesling'], 'i' => [2]],
            ['q' => 'What wine pairs well with aged hard cheese?', 'a' => 'Mature red wine', 'c' => ['Light white wine', 'Mature red wine', 'Sweet wine', 'Rosé'], 'i' => [1]],

            // General Knowledge
            ['q' => 'What are tannins in wine?', 'a' => 'Compounds from skins, seeds, and stems', 'c' => ['Sugar molecules', 'Compounds from skins, seeds, and stems', 'Alcohol byproducts', 'Added preservatives'], 'i' => [1]],
            ['q' => 'What causes the bubbles in sparkling wine?', 'a' => 'Carbon dioxide from fermentation', 'c' => ['Added carbonation', 'Carbon dioxide from fermentation', 'Chemical reaction', 'Shaking the bottle'], 'i' => [1]],
            ['q' => 'What is the difference between Old World and New World wines?', 'a' => 'Old World is Europe, New World elsewhere', 'c' => ['Age of the vineyard', 'Old World is Europe, New World elsewhere', 'Winemaking technique', 'Grape varieties used'], 'i' => [1]],
            ['q' => 'What is botrytis cinerea?', 'a' => 'Noble rot for sweet wines', 'c' => ['A wine fault', 'Noble rot for sweet wines', 'A grape disease', 'An aging process'], 'i' => [1]],
            ['q' => 'What is the purpose of oak aging?', 'a' => 'Add flavor and complexity', 'c' => ['Increase alcohol', 'Add flavor and complexity', 'Add carbonation', 'Increase acidity'], 'i' => [1]],
        ];

        // Select a question based on index, cycling through the pool
        $question_data = $questions[$index % count($questions)];

        return [
            'question' => $question_data['q'],
            'answer' => $question_data['a'],
            'choices' => $question_data['c'],
            'correct_indices' => $question_data['i'],
        ];
    }
}
