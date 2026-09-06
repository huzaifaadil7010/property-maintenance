<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;

class GetTotalMaintenanceRequestsByStatus
{
    /**
     * @param  array<MaintenanceRequestStatus>  $statuses
     */
    public static function handle(array $statuses): int
    {
        return MaintenanceRequest::query()
            ->whereIn('status', array_map(fn (MaintenanceRequestStatus $status): string => $status->value, $statuses))
            ->count();
    }
}
