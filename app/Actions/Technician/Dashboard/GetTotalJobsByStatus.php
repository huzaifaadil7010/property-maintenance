<?php

namespace App\Actions\Technician\Dashboard;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;

class GetTotalJobsByStatus
{
    public static function handle(
        MaintenanceRequestStatus $status,
        User $technician,
    ): int {
        return match ($status) {
            MaintenanceRequestStatus::ASSIGNED => MaintenanceRequest::query()
                ->whereBelongsTo($technician, 'assignedTechnician')
                ->assigned()
                ->count(),
            MaintenanceRequestStatus::IN_PROGRESS => MaintenanceRequest::query()
                ->whereBelongsTo($technician, 'assignedTechnician')
                ->inProgress()
                ->count(),
            MaintenanceRequestStatus::COMPLETED => MaintenanceRequest::query()
                ->whereBelongsTo($technician, 'assignedTechnician')
                ->completed()
                ->count(),
            default => 0,
        };
    }
}
