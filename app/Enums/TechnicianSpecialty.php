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

    public function getLabel(): string
    {
        return match ($this) {
            self::PLUMBING => 'Plumbing',
            self::ELECTRICAL => 'Electrical',
            self::AIR_CONDITIONING => 'Air conditioning',
            self::CARPENTRY => 'Carpentry',
            self::GENERAL_MAINTENANCE => 'General maintenance',
        };
    }
}
