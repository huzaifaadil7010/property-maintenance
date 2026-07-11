<?php

namespace App\Enums;

enum MaintenanceRequestStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
}
