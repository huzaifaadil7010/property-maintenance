<?php

namespace App\Actions\Organization\Technician;

use App\Data\TechnicianProfileData;
use App\Models\TechnicianProfile;

class CreateTechnicianProfile
{
    public static function handle(TechnicianProfileData $data): TechnicianProfile
    {
        return TechnicianProfile::create($data->toArray());
    }
}
