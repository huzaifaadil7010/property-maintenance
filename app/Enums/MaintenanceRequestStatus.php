<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum MaintenanceRequestStatus: string
{
    use EnumHelper;

    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
}
