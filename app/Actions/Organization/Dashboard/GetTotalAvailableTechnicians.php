<?php

namespace App\Actions\Organization\Dashboard;

use App\Models\User;

class GetTotalAvailableTechnicians
{
    public static function handle(): int
    {
        return User::query()
            ->technician()
            ->whereRelation('technicianProfiles', 'is_available', true)
            ->count();
    }
}
