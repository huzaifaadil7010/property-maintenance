<?php

namespace App\Data;

use App\Enums\MaintenanceRequestStatus;
use Spatie\LaravelData\Data;

class CompleteMaintenanceRequestData extends Data
{
    public function __construct(
        public MaintenanceRequestStatus $status,
        public string $completion_notes,
        public string $actual_cost,
        public array $images = [],
    ) {}
}
