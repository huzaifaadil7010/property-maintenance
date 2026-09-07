<?php

namespace App\Actions\Organization\Dashboard;

use App\Models\Unit;

class GetTotalVacantUnits
{
    public static function handle(): int
    {
        return Unit::query()
            ->vacant()
            ->count();
    }
}
