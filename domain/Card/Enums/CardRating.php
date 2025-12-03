<?php

declare(strict_types=1);

namespace Domain\Card\Enums;

enum CardRating: string
{
    case AGAIN = 'again';
    case HARD = 'hard';
    case GOOD = 'good';
    case EASY = 'easy';
}
