<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum UnitStatus: string
{
    use EnumHelper;

    case VACANT = 'vacant';
    case OCCUPIED = 'occupied';
    case UNDER_MAINTENANCE = 'under-maintenance';

    public function getLabel(): string
    {
        return match ($this) {
            self::VACANT => 'Vacant',
            self::OCCUPIED => 'Occupied',
            self::UNDER_MAINTENANCE => 'Under maintenance',
        };
    }
}
