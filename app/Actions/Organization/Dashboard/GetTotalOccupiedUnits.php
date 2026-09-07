<?php

namespace App\Actions\Organization\Dashboard;

use App\Models\Unit;

class GetTotalOccupiedUnits
{
    public static function handle(): int
    {
        return Unit::query()
            ->occupied()
            ->count();
    }
}
