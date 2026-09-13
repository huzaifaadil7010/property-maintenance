<?php

namespace App\Actions\Resident\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestStatusData;
use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReopenMaintenanceRequest
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $resident,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($maintenanceRequest, $data, $resident): MaintenanceRequest {
            $maintenanceRequest = MaintenanceRequest::query()
                ->whereKey($maintenanceRequest->getKey())
                ->whereBelongsTo($resident, 'resident')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $maintenanceRequest->isCompleted()) {
                throw ValidationException::withMessages(['cannot_submit' => __('Only completed requests can be reopened.')]);
            }

            $fromStatus = $maintenanceRequest->status;
            $maintenanceRequest->status = $data->status;
            $maintenanceRequest->save();
            $maintenanceRequest->statusLogs()->create(['changed_by' => $resident->id, 'from_status' => $fromStatus, 'to_status' => $data->status, 'notes' => $data->notes]);

            return $maintenanceRequest;
        });

        self::logActivity($maintenanceRequest, $resident);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $resident,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_REOPENED,
            'title' => 'Maintenance request reopened',
            'description' => Str::swap([
                ':resident' => $resident->name,
                ':request' => $maintenanceRequest->title,
            ], 'Resident :resident reopened maintenance request ":request".'),
            'subject' => $maintenanceRequest,
            'actor' => $resident,
            'organization' => $resident->currentOrganization,
        ]));
    }
}
