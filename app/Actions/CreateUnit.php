<?php

namespace App\Actions;

use App\Data\UnitData;
use App\Models\Unit;

class CreateUnit
{
    public static function handle(UnitData $data): Unit
    {
        return Unit::create($data->toArray());
    }
}
