<?php

namespace App\Data;

class AssignTechnicianChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $id,
        public int $assigned_technician_id,
        public ?string $notes = null,
    ) {}

}
