<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Admin\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed admin accounts.
     */
    public function run(): void
    {
        // Main admin account
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@vinorecall.com',
            'password' => Hash::make('password'),
        ]);

        // Backup admin account
        Admin::create([
            'name' => 'Admin Backup',
            'email' => 'admin-backup@vinorecall.com',
            'password' => Hash::make('password'),
        ]);
    }
}

