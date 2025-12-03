<?php

declare(strict_types=1);

namespace Domain\Card\Enums;

enum CardRating: string
{
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';
}
