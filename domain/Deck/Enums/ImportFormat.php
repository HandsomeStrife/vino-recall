<?php

declare(strict_types=1);

namespace Domain\Deck\Enums;

enum ImportFormat: string
{
    case APKG = 'apkg';
    case CSV = 'csv';
}

