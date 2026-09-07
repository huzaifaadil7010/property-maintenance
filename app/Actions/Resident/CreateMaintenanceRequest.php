<?php

namespace App\Actions\Resident;

use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Data\MaintenanceRequestData;
use App\Enums\UserRole;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use Illuminate\Support\Facades\DB;

class CreateMaintenanceRequest
{
    use HasMediaLibraryUploadHelpers;

    public static function handle(
        MaintenanceRequestData $data,
        User $resident,
    ): MaintenanceRequest {
        $maintenanceRequest = DB::transaction(function () use ($data, $resident): MaintenanceRequest {
            $occupancy = $resident->occupancies()
                ->active()
                ->with('unit')
                ->firstOrFail();

            return MaintenanceRequest::create([
                'organization_id' => $resident->current_organization_id,
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
            ->where('current_organization_id', $resident->current_organization_id)
            ->role(UserRole::OWNER)
            ->first();

        $organizationOwner?->notify(
            new MaintenanceRequestCreatedNotification($maintenanceRequest),
        );

        return $maintenanceRequest;
    }
}
