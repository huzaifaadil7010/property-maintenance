<?php

namespace App\Actions;

use App\Data\PropertyData;
use App\Models\Property;

class CreateProperty
{
    public static function handle(PropertyData $data): Property
    {
        return Property::create($data->toArray());
    }
}
