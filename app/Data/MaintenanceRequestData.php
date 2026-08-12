<?php

namespace App\Data;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use Spatie\LaravelData\Data;

class MaintenanceRequestData extends Data
{
    public function __construct(
        public string $title,
        public MaintenanceCategory $category,
        public MaintenancePriority $priority,
        public string $description,
    ) {}
}
