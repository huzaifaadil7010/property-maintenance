<?php

namespace App\Enums;

enum TechnicianSpecialty: string
{
    case PLUMBING = 'plumbing';
    case ELECTRICAL = 'electrical';
    case AIR_CONDITIONING = 'air-conditioning';
    case CARPENTRY = 'carpentry';
    case GENERAL_MAINTENANCE = 'general-maintenance';
}
