<?php

namespace App\Data;

use App\Enums\PropertyType;

class CreatePropertyChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public string $name,
        public PropertyType $type,
        public string $address,
        public string $city,
    ) {}

}
