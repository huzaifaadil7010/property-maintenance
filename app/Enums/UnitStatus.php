<?php

namespace App\Enums;

enum UnitStatus: string
{
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
