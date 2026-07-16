<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\UnitStatus;
use App\Models\Unit;

class GetTotalVacantUnits
{
    public static function handle(): int
    {
        return Unit::query()
            ->where('status', UnitStatus::VACANT->value)
            ->count();
    }
}
