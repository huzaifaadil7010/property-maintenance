<?php

namespace App\Actions\Technician\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestStatusData;
use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartMaintenanceRequestWork
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $technician,
        Organization $organization,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($maintenanceRequest, $data, $technician): MaintenanceRequest {
            $maintenanceRequest = MaintenanceRequest::query()
                ->whereKey($maintenanceRequest->getKey())
                ->whereBelongsTo($technician, 'assignedTechnician')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $maintenanceRequest->isAssigned()) {
                throw ValidationException::withMessages([
                    'cannot_submit' => __('Only assigned jobs can be started.'),
                ]);
            }

            $fromStatus = $maintenanceRequest->status;
            $maintenanceRequest->status = $data->status;
            $maintenanceRequest->save();
            $maintenanceRequest->statusLogs()->create([
                'changed_by' => $technician->id,
                'from_status' => $fromStatus,
                'to_status' => $data->status,
                'notes' => $data->notes,
            ]);

            return $maintenanceRequest;
        });

        self::logActivity($maintenanceRequest, $technician, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $technician,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_WORK_STARTED,
            'title' => __('Maintenance work started'),
            'description' => __('Technician :technician started work on maintenance request ":request".', [
                'technician' => $technician->name,
                'request' => $maintenanceRequest->title,
            ]),
            'subject' => $maintenanceRequest,
            'actor' => $technician,
            'organization' => $organization,
        ]));
    }
}
