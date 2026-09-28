<?php

namespace App\Data;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;

class CreateResidentMaintenanceRequestChangeData extends ResidentChangeInputData
{
    public function __construct(
        public string $title,
        public MaintenanceCategory $category,
        public MaintenancePriority $priority,
        public string $description,
        public array $images,
    ) {}
}
