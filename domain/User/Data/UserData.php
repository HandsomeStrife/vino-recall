<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Domain\User\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $email_verified_at,
        public ?string $locale,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            email_verified_at: $user->email_verified_at?->toDateTimeString(),
            locale: $user->locale,
            created_at: $user->created_at->toDateTimeString(),
            updated_at: $user->updated_at->toDateTimeString(),
        );
    }
}
