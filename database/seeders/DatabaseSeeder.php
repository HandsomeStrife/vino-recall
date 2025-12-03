<?php

namespace Database\Seeders;

use Domain\Admin\Models\Admin;
use Domain\Card\Actions\CreateCardAction;
use Domain\Deck\Actions\CreateDeckAction;
use Domain\Subscription\Models\Plan;
use Domain\User\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user in admins table
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@vinorecall.com',
            'password' => Hash::make('password'),
        ]);

        // Create regular users
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create plans
        Plan::create([
            'name' => 'Basic',
            'price' => 9.99,
            'features' => 'Access to WSET Level 1 content',
        ]);

        Plan::create([
            'name' => 'Premium',
            'price' => 19.99,
            'features' => 'Access to WSET Level 1 & 2 content',
        ]);

        // Create sample decks (created by admin)
        $wset1GrapeDeck = (new CreateDeckAction)->execute(
            name: 'WSET Level 1: Grape Varieties',
            description: 'Learn about the main grape varieties',
            is_active: true,
            created_by: $admin->id,
            category: 'wset1'
        );

        $wset1ServiceDeck = (new CreateDeckAction)->execute(
            name: 'WSET Level 1: Wine Service',
            description: 'Master wine service basics',
            is_active: true,
            created_by: $admin->id,
            category: 'wset1'
        );

        $wset2RegionsDeck = (new CreateDeckAction)->execute(
            name: 'WSET Level 2: Wine Regions',
            description: 'Explore major wine regions of the world',
            is_active: true,
            created_by: $admin->id,
            category: 'wset2'
        );

        $wset2TerminologyDeck = (new CreateDeckAction)->execute(
            name: 'WSET Level 2: Label Terminology',
            description: 'Understand wine label terms',
            is_active: true,
            created_by: $admin->id,
            category: 'wset2'
        );

        $wset2ProductionDeck = (new CreateDeckAction)->execute(
            name: 'WSET Level 2: Wine Production',
            description: 'Learn winemaking processes',
            is_active: true,
            created_by: $admin->id,
            category: 'wset2'
        );

        // WSET Level 1: Grape Varieties - Multiple Choice Cards
        (new CreateCardAction)->execute(
            deckId: $wset1GrapeDeck->id,
            question: 'Which grape variety is primarily used in Champagne production?',
            answer: 'Chardonnay, Pinot Noir, and Pinot Meunier',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Chardonnay, Pinot Noir, and Pinot Meunier', 'Cabernet Sauvignon and Merlot', 'Syrah and Grenache', 'Riesling and Gewürztraminer'],
            correctAnswerIndex: 0
        );

        (new CreateCardAction)->execute(
            deckId: $wset1GrapeDeck->id,
            question: 'What is the main grape variety in Chianti?',
            answer: 'Sangiovese',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Nebbiolo', 'Sangiovese', 'Barbera', 'Montepulciano'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset1GrapeDeck->id,
            question: 'Which white grape variety is known for high acidity and aromatic qualities?',
            answer: 'Sauvignon Blanc',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Chardonnay', 'Pinot Grigio', 'Sauvignon Blanc', 'Viognier'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset1GrapeDeck->id,
            question: 'Which red grape variety has soft tannins and flavors of red fruit?',
            answer: 'Merlot',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Cabernet Sauvignon', 'Syrah', 'Merlot', 'Malbec'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset1GrapeDeck->id,
            question: 'Which grape variety is known as "Pinot Gris" in France?',
            answer: 'Pinot Grigio',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Pinot Blanc', 'Pinot Grigio', 'Pinot Meunier', 'Pinot Nero'],
            correctAnswerIndex: 1
        );

        // WSET Level 1: Wine Service - Multiple Choice Cards
        (new CreateCardAction)->execute(
            deckId: $wset1ServiceDeck->id,
            question: 'What is the ideal serving temperature for full-bodied red wines?',
            answer: '15-18°C (59-64°F)',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['5-10°C (41-50°F)', '10-13°C (50-55°F)', '15-18°C (59-64°F)', '20-25°C (68-77°F)'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset1ServiceDeck->id,
            question: 'What is the ideal serving temperature for sparkling wines?',
            answer: '6-10°C (43-50°F)',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['6-10°C (43-50°F)', '10-13°C (50-55°F)', '13-16°C (55-61°F)', '16-18°C (61-64°F)'],
            correctAnswerIndex: 0
        );

        (new CreateCardAction)->execute(
            deckId: $wset1ServiceDeck->id,
            question: 'What does "Brut" mean on a sparkling wine label?',
            answer: 'Dry (low sugar content)',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Sweet', 'Semi-sweet', 'Dry (low sugar content)', 'Very sweet'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset1ServiceDeck->id,
            question: 'Which glass shape is best for full-bodied red wines?',
            answer: 'Large bowl-shaped glass',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Tall flute', 'Small narrow glass', 'Large bowl-shaped glass', 'Wide shallow glass'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset1ServiceDeck->id,
            question: 'What is the purpose of decanting wine?',
            answer: 'To separate wine from sediment and allow it to aerate',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['To chill the wine faster', 'To separate wine from sediment and allow it to aerate', 'To remove the cork', 'To add bubbles'],
            correctAnswerIndex: 1
        );

        // WSET Level 2: Wine Regions - Multiple Choice Cards
        (new CreateCardAction)->execute(
            deckId: $wset2RegionsDeck->id,
            question: 'Which region in Italy is famous for producing Barolo and Barbaresco?',
            answer: 'Piedmont',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Tuscany', 'Piedmont', 'Veneto', 'Sicily'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2RegionsDeck->id,
            question: 'Which French region is known for Pinot Noir and Chardonnay?',
            answer: 'Burgundy',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Bordeaux', 'Burgundy', 'Rhône Valley', 'Loire Valley'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2RegionsDeck->id,
            question: 'Which region is famous for Rioja wines?',
            answer: 'Spain',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Portugal', 'Spain', 'Italy', 'France'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2RegionsDeck->id,
            question: 'Which New Zealand region is renowned for Sauvignon Blanc?',
            answer: 'Marlborough',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Hawke\'s Bay', 'Central Otago', 'Marlborough', 'Waipara Valley'],
            correctAnswerIndex: 2
        );

        (new CreateCardAction)->execute(
            deckId: $wset2RegionsDeck->id,
            question: 'Which Bordeaux subregion is located on the Left Bank?',
            answer: 'Pauillac',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Pomerol', 'Saint-Émilion', 'Pauillac', 'Fronsac'],
            correctAnswerIndex: 2
        );

        // WSET Level 2: Label Terminology - Multiple Choice Cards
        (new CreateCardAction)->execute(
            deckId: $wset2TerminologyDeck->id,
            question: 'What does "Grand Cru" mean in Burgundy?',
            answer: 'The highest vineyard classification',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['A specific grape variety', 'The highest vineyard classification', 'A winemaking technique', 'A type of barrel aging'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2TerminologyDeck->id,
            question: 'What does "Reserva" indicate on a Spanish wine label?',
            answer: 'The wine has been aged for a minimum period',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['It\'s a sweet wine', 'The wine has been aged for a minimum period', 'It\'s made from organic grapes', 'It\'s a sparkling wine'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2TerminologyDeck->id,
            question: 'What does "Sur Lie" aging mean?',
            answer: 'Aging wine on its lees (dead yeast cells)',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Aging in stainless steel', 'Aging wine on its lees (dead yeast cells)', 'Aging in very old barrels', 'Aging in bottle for 5+ years'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2TerminologyDeck->id,
            question: 'What does "Vintage" mean on a wine label?',
            answer: 'The year the grapes were harvested',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['The year the wine was bottled', 'The year the grapes were harvested', 'The age of the vines', 'The winery\'s founding year'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2TerminologyDeck->id,
            question: 'What does "DOC" stand for on an Italian wine label?',
            answer: 'Denominazione di Origine Controllata',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Denominazione di Origine Controllata', 'Denominazione Originale Classico', 'Di Origine Certificata', 'Denominazione Organica Controllata'],
            correctAnswerIndex: 0
        );

        // WSET Level 2: Wine Production - Multiple Choice Cards
        (new CreateCardAction)->execute(
            deckId: $wset2ProductionDeck->id,
            question: 'What is the primary difference between red and white wine production?',
            answer: 'Red wine is fermented with grape skins',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Red wine uses red grapes only', 'Red wine is fermented with grape skins', 'Red wine ferments at higher temperatures', 'Red wine takes longer to ferment'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2ProductionDeck->id,
            question: 'What is malolactic fermentation?',
            answer: 'Converting malic acid to lactic acid',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Converting sugar to alcohol', 'Converting malic acid to lactic acid', 'Removing sediment from wine', 'Adding sulfites to wine'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2ProductionDeck->id,
            question: 'What is the traditional method for producing Champagne called?',
            answer: 'Méthode Champenoise',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Méthode Traditionnelle', 'Méthode Champenoise', 'Charmat Method', 'Transfer Method'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2ProductionDeck->id,
            question: 'What does "oak aging" add to wine?',
            answer: 'Flavors like vanilla, toast, and complexity',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['More alcohol content', 'Flavors like vanilla, toast, and complexity', 'Natural carbonation', 'Higher acidity'],
            correctAnswerIndex: 1
        );

        (new CreateCardAction)->execute(
            deckId: $wset2ProductionDeck->id,
            question: 'What is "carbonic maceration" used for?',
            answer: 'Producing light, fruity red wines like Beaujolais Nouveau',
            cardType: \Domain\Card\Enums\CardType::MULTIPLE_CHOICE,
            answerChoices: ['Making sparkling wines', 'Producing light, fruity red wines like Beaujolais Nouveau', 'Creating sweet dessert wines', 'Aging white wines'],
            correctAnswerIndex: 1
        );
    }
}
