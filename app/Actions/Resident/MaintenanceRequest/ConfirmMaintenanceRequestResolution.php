<?php

namespace App\Actions\Resident\MaintenanceRequest;

use App\Data\MaintenanceRequestStatusData;
use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmMaintenanceRequestResolution
{
    public static function handle(MaintenanceRequest $maintenanceRequest, MaintenanceRequestStatusData $data, User $resident): MaintenanceRequest
    {
        return DB::transaction(function () use ($maintenanceRequest, $data, $resident): MaintenanceRequest {
            $maintenanceRequest = MaintenanceRequest::query()
                ->whereKey($maintenanceRequest->getKey())
                ->whereBelongsTo($resident, 'resident')
                ->lockForUpdate()
                ->firstOrFail();

            if ($maintenanceRequest->status !== MaintenanceRequestStatus::COMPLETED) {
                throw ValidationException::withMessages(['cannot_submit' => __('Only completed requests can be confirmed.')]);
            }

            $fromStatus = $maintenanceRequest->status;
            $maintenanceRequest->status = $data->status;
            $maintenanceRequest->closed_at = now();
            $maintenanceRequest->save();
            $maintenanceRequest->statusLogs()->create(['changed_by' => $resident->id, 'from_status' => $fromStatus, 'to_status' => $data->status, 'notes' => $data->notes]);

            return $maintenanceRequest;
        });
    }
}
