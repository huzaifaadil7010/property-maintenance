<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ResidentUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {}
}
