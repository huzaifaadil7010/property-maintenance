<?php

namespace App\Actions\Organization\Property;

use App\Data\PropertyData;
use App\Models\Property;

class UpdateProperty
{
    public static function handle(Property $property, PropertyData $data): bool
    {
        return $property->update($data->toArray());
    }
}
