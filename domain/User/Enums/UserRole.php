<?php

declare(strict_types=1);

namespace Domain\User\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
