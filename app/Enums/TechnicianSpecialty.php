<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum TechnicianSpecialty: string
{
    use EnumHelper;

    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case AIR_CONDITIONING = 'air-conditioning';
    case CARPENTRY = 'carpentry';
    case GENERAL_MAINTENANCE = 'general-maintenance';
}
