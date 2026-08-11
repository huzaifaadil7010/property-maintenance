<?php

namespace App\Actions\Organization\Resident;

use App\Data\ResidentUserData;
use App\Models\User;

class CreateUser
{
    public static function handle(ResidentUserData $data): User
    {
        return User::create($data->toArray());
    }
}
