<?php

namespace App\Actions\Organization\Resident;

use App\Data\OccupancyData;
use App\Models\Occupancy;

class CreateOccupancy
{
    public static function handle(OccupancyData $data): Occupancy
    {
        return Occupancy::create($data->toArray());
    }
}
