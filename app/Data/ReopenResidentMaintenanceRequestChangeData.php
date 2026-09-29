<?php

namespace App\Data;

class ReopenResidentMaintenanceRequestChangeData extends ResidentChangeInputData
{
    public function __construct(
        public int $id,
        public string $notes,
    ) {}
}
