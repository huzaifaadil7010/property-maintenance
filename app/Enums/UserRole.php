<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum UserRole: string
{
    use EnumHelper;

    case OWNER = 'owner';
    case MANAGER = 'manager';
    case RESIDENT = 'resident';
    case TECHNICIAN = 'technician';
}
