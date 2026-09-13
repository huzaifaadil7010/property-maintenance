<?php

namespace App\Actions\Organization\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\AssignMaintenanceRequestTechnicianData;
use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\MaintenanceRequestAssignedNotification;
use Illuminate\Support\Facades\DB;

class AssignMaintenanceRequestTechnician
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        AssignMaintenanceRequestTechnicianData $data,
        User $user,
        Organization $organization,
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

        self::logActivity($maintenanceRequest, $user, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED,
            'title' => 'Technician assigned',
            'description' => sprintf(
                '%s assigned %s to maintenance request "%s".',
                $actor->name,
                $maintenanceRequest->assignedTechnician?->name ?? 'a technician',
                $maintenanceRequest->title,
            ),
            'subject' => $maintenanceRequest,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
