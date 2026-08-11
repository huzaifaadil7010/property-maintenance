<?php

namespace App\Data;

use App\Enums\OccupancyStatus;
use Spatie\LaravelData\Data;

class OccupancyData extends Data
{
    public function __construct(
        public int $organization_id,
        public int $unit_id,
        public int $resident_id,
        public string $starts_at,
        public OccupancyStatus $status,
    ) {}
}
