<?php

namespace App\Actions\Organization\Technician;

use App\Data\TechnicianUserData;
use App\Models\User;

class CreateUser
{
    public static function handle(TechnicianUserData $data): User
    {
        return User::create($data->toArray());
    }
}
