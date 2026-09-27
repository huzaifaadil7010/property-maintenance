<?php

namespace App\Data;

use App\Enums\UnitStatus;

class UpdateUnitChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $id,
        public int $property_id,
        public string $name,
        public UnitStatus $status,
        public ?string $floor = null,
    ) {}

}
