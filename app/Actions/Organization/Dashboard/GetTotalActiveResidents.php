<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\OccupancyStatus;
use App\Models\User;

class GetTotalActiveResidents
{
    public static function handle(): int
    {
        return User::query()
            ->resident()
            ->whereRelation('occupancies', 'status', OccupancyStatus::ACTIVE->value)
            ->count();
    }
}
