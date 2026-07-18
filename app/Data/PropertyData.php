<?php

namespace App\Data;

use App\Enums\PropertyType;
use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public string $name,
        public PropertyType $type,
        public string $address,
        public string $city,
    ) {}
}
