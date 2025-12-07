<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Spatie\LaravelData\Data;

class ImportResultData extends Data
{
    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        public int $imported,
        public int $skipped,
        public array $errors,
    ) {}
}

