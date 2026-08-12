<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum MaintenanceCategory: string
{
    use EnumHelper;

    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case AIR_CONDITIONING = 'air-conditioning';
    case CARPENTRY = 'carpentry';
    case GENERAL = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::PLUMBING => 'Plumbing',
            self::ELECTRICAL => 'Electrical',
            self::AIR_CONDITIONING => 'Air conditioning',
            self::CARPENTRY => 'Carpentry',
            self::GENERAL => 'General',
        };
    }
}
