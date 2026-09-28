<?php

namespace App\Data;

use App\Enums\TechnicianSpecialty;

class CreateTechnicianChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public string $name,
        public string $email,
        public TechnicianSpecialty $specialty,
        public bool $is_available = true,
        public ?string $phone = null,
    ) {}

}
