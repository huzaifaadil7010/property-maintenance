<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\UnitStatus;
use App\Models\Unit;

class GetTotalOccupiedUnits
{
    public static function handle(): int
    {
        return Unit::query()
            ->where('status', UnitStatus::OCCUPIED->value)
            ->count();
    }
}
