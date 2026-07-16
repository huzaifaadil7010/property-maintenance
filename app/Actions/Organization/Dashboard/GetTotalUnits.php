<?php

namespace App\Actions\Organization\Dashboard;

use App\Models\Unit;

class GetTotalUnits
{
    public static function handle(): int
    {
        return Unit::query()->count();
    }
}
