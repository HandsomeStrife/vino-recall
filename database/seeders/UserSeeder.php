<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed user accounts.
     */
    public function run(): void
    {
        // Fixed test user for consistent login
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create 29 additional fake users (total 30)
        User::factory()->count(29)->create();
    }
}

