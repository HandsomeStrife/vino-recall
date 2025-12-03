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
        $wset1Deck = (new CreateDeckAction)->execute(
            name: 'WSET Level 1: Foundation',
            description: 'Foundation level wine education',
            is_active: true,
            created_by: $admin->id
        );

        $wset2Deck = (new CreateDeckAction)->execute(
            name: 'WSET Level 2: Intermediate',
            description: 'Intermediate level wine education',
            is_active: true,
            created_by: $admin->id
        );

        // Create sample cards for WSET Level 1
        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What is the primary grape variety used in Champagne?',
            answer: 'Chardonnay, Pinot Noir, and Pinot Meunier'
        );

        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What region is known for producing Barolo?',
            answer: 'Piedmont, Italy'
        );

        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What is the ideal serving temperature for red wine?',
            answer: 'Room temperature (15-18°C / 59-64°F)'
        );

        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What is the ideal serving temperature for white wine?',
            answer: 'Chilled (7-13°C / 45-55°F)'
        );

        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What does "Brut" mean on a Champagne label?',
            answer: 'Dry (lowest sugar content)'
        );

        (new CreateCardAction)->execute(
            deckId: $wset1Deck->id,
            question: 'What is the main grape variety in Chianti?',
            answer: 'Sangiovese'
        );

        // Create sample cards for WSET Level 2
        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What does "Grand Cru" mean on a Burgundy wine label?',
            answer: 'It indicates a vineyard of the highest quality classification'
        );

        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What is the difference between Premier Cru and Grand Cru in Burgundy?',
            answer: 'Premier Cru is a high-quality vineyard classification, while Grand Cru is the highest classification reserved for the best vineyards'
        );

        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What is the primary grape variety in Bordeaux red wines?',
            answer: 'Cabernet Sauvignon and Merlot (blended)'
        );

        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What does "Reserva" mean on a Spanish wine label?',
            answer: 'The wine has been aged for a minimum period (red: 3 years, white/rosé: 2 years)'
        );

        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What is the main grape variety in Rioja?',
            answer: 'Tempranillo'
        );

        (new CreateCardAction)->execute(
            deckId: $wset2Deck->id,
            question: 'What does "Vintage" mean on a Champagne label?',
            answer: 'The wine is made from grapes harvested in a single year (non-vintage blends multiple years)'
        );
    }
}
