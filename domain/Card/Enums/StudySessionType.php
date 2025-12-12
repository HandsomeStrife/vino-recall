<?php

declare(strict_types=1);

namespace Domain\Card\Enums;

enum StudySessionType: string
{
    case NORMAL = 'normal';
    case DEEP_STUDY = 'deep_study';
    case PRACTICE = 'practice';
}
