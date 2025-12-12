<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\Data\AdminData;
use Domain\Admin\Models\Admin;

class CreateAdminAction
{
    public function execute(string $name, string $email, string $password): AdminData
    {
        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        return AdminData::fromModel($admin);
    }
}
