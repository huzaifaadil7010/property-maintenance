<?php

namespace App\Actions\Organization\MaintenanceRequest;

use App\Data\AssignMaintenanceRequestTechnicianData;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestAssignedNotification;
use Illuminate\Support\Facades\DB;

class AssignMaintenanceRequestTechnician
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        AssignMaintenanceRequestTechnicianData $data,
        User $user,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($maintenanceRequest, $data, $user): MaintenanceRequest {
            $fromStatus = $maintenanceRequest->status;

            $maintenanceRequest->assigned_technician_id = $data->assigned_technician_id;
            $maintenanceRequest->status = $data->status;
            $maintenanceRequest->save();

            $maintenanceRequest->statusLogs()->create([
                'changed_by' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $data->status,
                'notes' => $data->notes,
            ]);

            return $maintenanceRequest;
        });

        $maintenanceRequest->assignedTechnician?->notify(
            new MaintenanceRequestAssignedNotification($maintenanceRequest),
        );

        return $maintenanceRequest;
    }
}
