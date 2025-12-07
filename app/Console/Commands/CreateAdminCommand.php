<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--name= : The name of the admin}
                            {--email= : The email of the admin}
                            {--password= : The password of the admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? text(
            label: 'What is the admin name?',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 2 => 'Name must be at least 2 characters.',
                strlen($value) > 255 => 'Name must not exceed 255 characters.',
                default => null,
            }
        );

        $email = $this->option('email') ?? text(
            label: 'What is the admin email?',
            required: true,
            validate: function (string $value) {
                $validator = Validator::make(['email' => $value], [
                    'email' => 'email',
                ]);

                if ($validator->fails()) {
                    return 'Please enter a valid email address.';
                }

                if (Admin::where('email', $value)->exists()) {
                    return 'An admin with this email already exists.';
                }

                return null;
            }
        );

        $password = $this->option('password') ?? password(
            label: 'What is the admin password?',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 8 => 'Password must be at least 8 characters.',
                default => null,
            }
        );

        // Check if email already exists (for non-interactive mode)
        if ($this->option('email') && Admin::where('email', $email)->exists()) {
            $this->error('An admin with this email already exists.');

            return Command::FAILURE;
        }

        $admin_data = (new CreateAdminAction())->execute($name, $email, $password);

        $this->info("Admin user created successfully!");
        $this->table(
            ['ID', 'Name', 'Email'],
            [[$admin_data->id, $admin_data->name, $admin_data->email]]
        );

        return Command::SUCCESS;
    }
}

