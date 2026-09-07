<?php

namespace App\Actions\Resident\Dashboard;

use App\Models\MaintenanceRequest;
use App\Models\User;

class GetTotalMaintenanceRequestsByStatus
{
    public static function handle(array $statuses, User $resident): int
    {
        return MaintenanceRequest::query()
            ->whereBelongsTo($resident, 'resident')
            ->whereIn('status', $statuses)
            ->count();
    }
}
