<?php

namespace App\Data;

class CreateResidentChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public string $name,
        public string $email,
        public int $property_id,
        public int $unit_id,
        public ?string $phone = null,
    ) {}

}
