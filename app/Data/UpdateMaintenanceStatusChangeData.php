<?php

namespace App\Data;

use App\Enums\MaintenanceRequestStatus;

class UpdateMaintenanceStatusChangeData extends OrganizationChangeInputData
{
    public function __construct(
        public int $id,
        public MaintenanceRequestStatus $status,
        public ?string $notes = null,
    ) {}

}
