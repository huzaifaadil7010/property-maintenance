<?php

namespace App\Actions\Technician\MaintenanceRequest;

use App\Data\MaintenanceRequestStatusData;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartMaintenanceRequestWork
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $technician,
    ): MaintenanceRequest {
        return DB::transaction(function () use ($maintenanceRequest, $data, $technician): MaintenanceRequest {
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
    }
}
