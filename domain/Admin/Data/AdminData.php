<?php

declare(strict_types=1);

namespace Domain\Admin\Data;

use Domain\Admin\Models\Admin;
use Spatie\LaravelData\Data;

class AdminData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $email_verified_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Admin $admin): self
    {
        return new self(
            id: $admin->id,
            name: $admin->name,
            email: $admin->email,
            email_verified_at: $admin->email_verified_at?->toDateTimeString(),
            created_at: $admin->created_at->toDateTimeString(),
            updated_at: $admin->updated_at->toDateTimeString(),
        );
    }
}
