<?php

namespace App\Enums;

enum UnitStatus: string
{
    case VACANT = 'vacant';
    case OCCUPIED = 'occupied';
    case UNDER_MAINTENANCE = 'under-maintenance';
}
