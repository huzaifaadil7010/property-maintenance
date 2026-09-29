<?php

namespace App\Data;

class ConfirmResidentResolutionChangeData extends ResidentChangeInputData
{
    public function __construct(
        public int $id,
        public ?string $notes = null,
    ) {}
}
