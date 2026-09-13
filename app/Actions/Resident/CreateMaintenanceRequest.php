<?php

namespace App\Actions\Resident;

use App\Actions\Common\LogActivity;
use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Data\ActivityLogData;
use App\Data\MaintenanceRequestData;
use App\Enums\ActivityEventEnum;
use App\Enums\UserRole;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMaintenanceRequest
{
    use HasMediaLibraryUploadHelpers;

    public static function handle(
        MaintenanceRequestData $data,
        User $resident,
    ): MaintenanceRequest {
        $organization = $resident->currentOrganization;

        $maintenanceRequest = DB::transaction(function () use ($data, $resident, $organization): MaintenanceRequest {
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

        self::logActivity($maintenanceRequest, $resident);

        return $maintenanceRequest;
    }

    private static function logActivity(
        MaintenanceRequest $maintenanceRequest,
        User $resident,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::MAINTENANCE_REQUEST_CREATED,
            'title' => 'Maintenance request created',
            'description' => Str::swap([
                ':resident' => $resident->name,
                ':request' => $maintenanceRequest->title,
            ], 'Resident :resident created maintenance request ":request".'),
            'subject' => $maintenanceRequest,
            'actor' => $resident,
            'organization' => $resident->currentOrganization,
        ]));
    }
}
