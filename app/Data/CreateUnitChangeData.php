<?php

namespace App\Data;

use App\Enums\UnitStatus;

class CreateUnitChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $property_id,
        public string $name,
        public UnitStatus $status,
        public ?string $floor = null,
    ) {}

}
