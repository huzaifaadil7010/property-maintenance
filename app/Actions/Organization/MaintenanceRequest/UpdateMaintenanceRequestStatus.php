<?php

namespace App\Actions\Organization\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestStatusData;
use App\Enums\ActivityEventEnum;
use App\Enums\MaintenanceRequestStatus as MaintenanceRequestStatusEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateMaintenanceRequestStatus
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $user,
        Organization $organization,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($maintenanceRequest, $data, $user): MaintenanceRequest {
            $fromStatus = $maintenanceRequest->status;

            $maintenanceRequest->status = $data->status;

            if ($data->status === MaintenanceRequestStatusEnum::COMPLETED) {
                $maintenanceRequest->completed_at = now();
            }

            if ($data->status === MaintenanceRequestStatusEnum::CLOSED) {
                $maintenanceRequest->closed_at = now();
            }

            $maintenanceRequest->save();

            $maintenanceRequest->statusLogs()->create([
                'changed_by' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $data->status,
                'notes' => $data->notes,
            ]);

            return $maintenanceRequest;
        });

        self::logActivity($maintenanceRequest, $user, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_STATUS_UPDATED,
            'title' => 'Maintenance request status updated',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':request' => $maintenanceRequest->title,
                ':status' => $maintenanceRequest->status->getLabel(),
            ], ':actor changed maintenance request ":request" to :status.'),
            'subject' => $maintenanceRequest,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
