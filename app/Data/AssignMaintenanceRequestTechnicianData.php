<?php

namespace App\Data;

use App\Enums\MaintenanceRequestStatus;
use Spatie\LaravelData\Data;

class AssignMaintenanceRequestTechnicianData extends Data
{
    public function __construct(
        public int $assigned_technician_id,
        public MaintenanceRequestStatus $status,
        public ?string $notes = null,
    ) {}
}
