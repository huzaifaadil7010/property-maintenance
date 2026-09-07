<?php

namespace App\Actions\Resident\Dashboard;

use App\Enums\OccupancyStatus;
use App\Models\Occupancy;
use App\Models\User;

class GetCurrentResidence
{
    public static function handle(User $resident): ?Occupancy
    {
        return $resident->occupancies()
            ->select(['id', 'unit_id'])
            ->where('status', OccupancyStatus::ACTIVE)
            ->with(['unit:id,property_id,name', 'unit.property:id,name'])
            ->latest('id')
            ->first();
    }
}
