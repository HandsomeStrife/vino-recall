<?php

declare(strict_types=1);

namespace Domain\Admin\Repositories;

use Domain\Admin\Data\AdminData;
use Domain\Admin\Models\Admin;
use Illuminate\Support\Collection;

class AdminRepository
{
    public function findByEmail(string $email): ?AdminData
    {
        $admin = Admin::where('email', $email)->first();

        return $admin ? AdminData::fromModel($admin) : null;
    }

    public function findById(int $id): ?AdminData
    {
        $admin = Admin::find($id);

        return $admin ? AdminData::fromModel($admin) : null;
    }

    public function getLoggedInAdmin(): AdminData
    {
        $admin = auth()->guard('admin')->user();

        if (! $admin) {
            throw new \RuntimeException('No authenticated admin found');
        }

        return AdminData::fromModel($admin);
    }

    /**
     * @return Collection<AdminData>
     */
    public function getAll(): Collection
    {
        return Admin::all()->map(fn ($admin) => AdminData::fromModel($admin));
    }
}
