<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum OccupancyStatus: string
{
    use EnumHelper;

    case ACTIVE = 'active';
    case ENDED = 'ended';
}
