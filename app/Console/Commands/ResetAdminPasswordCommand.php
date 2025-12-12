<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Admin\Models\Admin;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\search;

class ResetAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password
                            {--email= : The email of the admin}
                            {--password= : The new password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset an admin user password';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? search(
            label: 'Search for admin by email',
            options: function (string $value) {
                if (strlen($value) < 1) {
                    return Admin::pluck('email', 'email')->toArray();
                }

                return Admin::where('email', 'like', "%{$value}%")
                    ->pluck('email', 'email')
                    ->toArray();
            },
            placeholder: 'Start typing to search...'
        );

        $admin = Admin::where('email', $email)->first();

        if (! $admin) {
            $this->error("No admin found with email: {$email}");

            return Command::FAILURE;
        }

        $password = $this->option('password') ?? password(
            label: 'Enter the new password',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 8 => 'Password must be at least 8 characters.',
                default => null,
            }
        );

        $admin->update(['password' => $password]);

        $this->info("Password reset successfully for admin: {$admin->name} ({$admin->email})");

        return Command::SUCCESS;
    }
}
