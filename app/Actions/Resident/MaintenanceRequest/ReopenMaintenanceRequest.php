<?php

namespace App\Actions\Resident\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestStatusData;
use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReopenMaintenanceRequest
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $resident,
        Organization $organization,
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

        self::logActivity($maintenanceRequest, $resident, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $resident,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_REOPENED,
            'title' => __('Maintenance request reopened'),
            'description' => __('Resident :resident reopened maintenance request ":request".', [
                'resident' => $resident->name,
                'request' => $maintenanceRequest->title,
            ]),
            'subject' => $maintenanceRequest,
            'actor' => $resident,
            'organization' => $organization,
        ]));
    }
}
