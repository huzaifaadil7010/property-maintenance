<?php

namespace App\Data;

class DeleteUnitChangeData extends OrganizationChangeInputData
{
    public function __construct(public int $id) {}

}
