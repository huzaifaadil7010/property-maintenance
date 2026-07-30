<?php

namespace App\Data;

use App\Enums\MaintenanceRequestStatus;
use Spatie\LaravelData\Data;

class MaintenanceRequestStatusData extends Data
{
    public function __construct(
        public MaintenanceRequestStatus $status,
        public ?string $notes = null,
    ) {}
}
