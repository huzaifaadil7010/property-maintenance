<?php

namespace App\Actions;

use App\Data\UnitData;
use App\Models\Unit;

class UpdateUnit
{
    public static function handle(Unit $unit, UnitData $data): bool
    {
        return $unit->update($data->toArray());
    }
}
