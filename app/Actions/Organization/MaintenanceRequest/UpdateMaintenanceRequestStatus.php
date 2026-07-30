<?php

namespace App\Actions\Organization\MaintenanceRequest;

use App\Data\MaintenanceRequestStatusData;
use App\Enums\MaintenanceRequestStatus as MaintenanceRequestStatusEnum;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateMaintenanceRequestStatus
{
    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        MaintenanceRequestStatusData $data,
        User $user,
    ): MaintenanceRequest {
        return DB::transaction(function () use ($maintenanceRequest, $data, $user): MaintenanceRequest {
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
    }
}
