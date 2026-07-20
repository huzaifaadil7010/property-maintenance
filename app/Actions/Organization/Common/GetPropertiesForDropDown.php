<?php

namespace App\Actions\Organization\Common;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;

class GetPropertiesForDropDown
{
    public static function handle(): Collection
    {
        return Property::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
}
