<?php

namespace App\Actions\Technician\MaintenanceRequest;

use App\Models\MaintenanceRequest;
use App\Models\User;

class GetMyJob
{
    public static function handle(MaintenanceRequest $maintenanceRequest, User $technician): MaintenanceRequest
    {
        return MaintenanceRequest::query()
            ->whereKey($maintenanceRequest->getKey())
            ->whereBelongsTo($technician, 'assignedTechnician')
            ->with([
                'property:id,name',
                'unit:id,name',
                'resident:id,name,email,phone',
                'assignedTechnician:id,name',
                'media',
                'statusLogs.changedBy:id,name',
            ])
            ->firstOrFail();
    }
}
