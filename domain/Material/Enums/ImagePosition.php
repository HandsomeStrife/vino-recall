<?php

declare(strict_types=1);

namespace Domain\Material\Enums;

enum ImagePosition: string
{
    case TOP = 'top';
    case LEFT = 'left';
    case RIGHT = 'right';
    case BOTTOM = 'bottom';

    public function label(): string
    {
        return match ($this) {
            self::TOP => 'Top',
            self::LEFT => 'Left',
            self::RIGHT => 'Right',
            self::BOTTOM => 'Bottom',
        };
    }
}
