<?php

namespace App\Actions\Resident;

use App\Actions\Common\LogActivity;
use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestData;
use App\Enums\ActivityEventEnum;
use App\Enums\UserRole;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use Illuminate\Support\Facades\DB;

class CreateMaintenanceRequest
{
    use HasMediaLibraryUploadHelpers;

    public static function handle(
        MaintenanceRequestData $data,
        User $resident,
        Organization $organization,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($data, $resident): MaintenanceRequest {
            $occupancy = $resident->occupancies()
                ->active()
                ->with('unit')
                ->firstOrFail();

            return MaintenanceRequest::create([
                'organization_id' => $organization->id,
                'property_id' => $occupancy->unit->property_id,
                'unit_id' => $occupancy->unit_id,
                'resident_id' => $resident->id,
                'title' => $data->title,
                'category' => $data->category,
                'priority' => $data->priority,
                'description' => $data->description,
            ]);
        });

        foreach ($data->images as $fileName) {
            self::moveMediaFromTempToPermanent(
                $fileName,
                $resident,
                $maintenanceRequest,
                MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES,
            );
        }

        $organizationOwner = User::query()
            ->where('current_organization_id', $organization->id)
            ->role(UserRole::OWNER)
            ->first();

        $organizationOwner?->notify(
            new MaintenanceRequestCreatedNotification($maintenanceRequest),
        );

        self::logActivity($maintenanceRequest, $resident, $organization);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $resident,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_CREATED,
            'title' => __('Maintenance request created'),
            'description' => __('Resident :resident created maintenance request ":request".', [
                'resident' => $resident->name,
                'request' => $maintenanceRequest->title,
            ]),
            'subject' => $maintenanceRequest,
            'actor' => $resident,
            'organization' => $organization,
        ]));
    }
}
