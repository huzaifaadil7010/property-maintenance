<?php

namespace App\Data;

class DeletePropertyChangeData extends OrganizationChangeInputData
{
    public function __construct(public int $id) {}

}
