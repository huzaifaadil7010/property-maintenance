<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum MaintenancePriority: string
{
    use EnumHelper;

    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';
}
