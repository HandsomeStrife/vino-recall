<?php

declare(strict_types=1);

namespace Domain\Deck\Enums;

enum ImportFormat: string
{
    case CSV = 'csv';
    case TXT = 'txt';

    public function label(): string
    {
        return match ($this) {
            self::CSV => 'CSV (Comma-Separated Values)',
            self::TXT => 'TXT (Tab-Separated Values)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CSV => 'Standard CSV format with comma-separated columns',
            self::TXT => 'Plain text format with tab-separated columns',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::CSV => 'csv',
            self::TXT => 'txt',
        };
    }
}
