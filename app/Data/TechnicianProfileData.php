<?php

namespace App\Data;

use App\Enums\TechnicianSpecialty;
use Spatie\LaravelData\Data;

class TechnicianProfileData extends Data
{
    public function __construct(
        public int $organization_id,
        public int $user_id,
        public TechnicianSpecialty $specialty,
        public bool $is_available,
        public ?string $phone = null,
    ) {}
}
