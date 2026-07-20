<?php

namespace App\Data;

use App\Enums\UnitStatus;
use Spatie\LaravelData\Data;

class UnitData extends Data
{
    public function __construct(
        public int $property_id,
        public string $name,
        public ?string $floor,
        public UnitStatus $status,
    ) {}
}
