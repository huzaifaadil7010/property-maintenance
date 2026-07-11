<?php

namespace App\Enums;

enum MaintenanceCategory: string
{
    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case AIR_CONDITIONING = 'air-conditioning';
    case CARPENTRY = 'carpentry';
    case GENERAL = 'general';
}
