<?php

namespace App\Actions\Technician\MaintenanceRequest;

use App\Actions\Common\LogActivity;
use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Data\ActivityLogData;
use App\Data\CompleteMaintenanceRequestData;
use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\MaintenanceRequestCompletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteMaintenanceRequestWork
{
    use HasMediaLibraryUploadHelpers;

    public static function handle(
        MaintenanceRequest $maintenanceRequest,
        CompleteMaintenanceRequestData $data,
        User $technician,
        Organization $organization,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($maintenanceRequest, $data, $technician): MaintenanceRequest {
            $maintenanceRequest = MaintenanceRequest::query()
                ->whereKey($maintenanceRequest->getKey())
                ->whereBelongsTo($technician, 'assignedTechnician')
                ->with('resident')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $maintenanceRequest->isInProgress()) {
                throw ValidationException::withMessages([
                    'cannot_submit' => __('Only in-progress jobs can be completed.'),
                ]);
            }

            $fromStatus = $maintenanceRequest->status;
            $maintenanceRequest->status = $data->status;
            $maintenanceRequest->completion_notes = $data->completion_notes;
            $maintenanceRequest->actual_cost = $data->actual_cost;
            $maintenanceRequest->completed_at = now();
            $maintenanceRequest->save();

            foreach ($data->images as $image) {
                self::moveMediaFromTempToPermanent(
                    $image,
                    $technician,
                    $maintenanceRequest,
                    MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES,
                );
            }

            $maintenanceRequest->statusLogs()->create([
                'changed_by' => $technician->id,
                'from_status' => $fromStatus,
                'to_status' => $data->status,
                'notes' => $data->completion_notes,
            ]);

            return $maintenanceRequest;
        });

        $maintenanceRequest->resident->notify(
            (new MaintenanceRequestCompletedNotification($maintenanceRequest))->afterCommit(),
        );

        self::logActivity($maintenanceRequest, $technician, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $technician,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_WORK_COMPLETED,
            'title' => 'Maintenance work completed',
            'description' => sprintf(
                'Technician %s completed maintenance request "%s".',
                $technician->name,
                $maintenanceRequest->title,
            ),
            'subject' => $maintenanceRequest,
            'actor' => $technician,
            'organization' => $organization,
        ]));
    }
}
