<?php

namespace App\Data;

use App\Enums\PropertyType;

class UpdatePropertyChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $id,
        public string $name,
        public PropertyType $type,
        public string $address,
        public string $city,
    ) {}

}
