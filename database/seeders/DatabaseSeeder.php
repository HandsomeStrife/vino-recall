<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Subscription\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in dependency order
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            DeckSeeder::class,
            CardSeeder::class,
            MaterialSeeder::class,
        ]);

        // Create subscription plans
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

    }
}
