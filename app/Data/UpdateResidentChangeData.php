<?php

namespace App\Data;

class UpdateResidentChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public int $property_id,
        public int $unit_id,
        public ?string $phone = null,
    ) {}
}
