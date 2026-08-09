<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ResidentData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public int $property_id,
        public int $unit_id,
        public ?string $phone = null,
    ) {}
}
