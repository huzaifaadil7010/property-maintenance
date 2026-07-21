<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\UserRole;
use App\Models\User;

class GetTotalAvailableTechnicians
{
    public static function handle(): int
    {
        return User::query()
            ->role(UserRole::TECHNICIAN)
            ->whereRelation('technicianProfiles', 'is_available', true)
            ->count();
    }
}
