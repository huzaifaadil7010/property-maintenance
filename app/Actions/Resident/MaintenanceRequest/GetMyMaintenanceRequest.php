<?php

namespace App\Actions\Resident\MaintenanceRequest;

use App\Models\MaintenanceRequest;
use App\Models\User;

class GetMyMaintenanceRequest
{
    public static function handle(MaintenanceRequest $maintenanceRequest, User $resident): MaintenanceRequest
    {
        return MaintenanceRequest::query()
            ->whereKey($maintenanceRequest->getKey())
            ->whereBelongsTo($resident, 'resident')
            ->with([
                'property:id,name',
                'unit:id,name',
                'resident:id,name',
                'assignedTechnician:id,name',
                'media',
                'statusLogs.changedBy:id,name',
            ])
            ->firstOrFail();
    }
}
