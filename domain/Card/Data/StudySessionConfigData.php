<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Domain\Card\Enums\StudySessionType;
use Spatie\LaravelData\Data;

class StudySessionConfigData extends Data
{
    public function __construct(
        public StudySessionType $type,
        public ?int $cardLimit = null,
        public ?array $statusFilters = null,
        public bool $trackSrs = true,
        public bool $randomOrder = false,
    ) {
    }
}

