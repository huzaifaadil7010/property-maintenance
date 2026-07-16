<?php

namespace App\Actions\Organization\Dashboard;

use App\Models\Property;

class GetTotalProperties
{
    public static function handle(): int
    {
        return Property::query()->count();
    }
}
