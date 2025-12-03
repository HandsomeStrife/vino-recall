<?php

declare(strict_types=1);

namespace Domain\Card\Enums;

enum CardType: string
{
    case TRADITIONAL = 'traditional';
    case MULTIPLE_CHOICE = 'multiple_choice';
}

