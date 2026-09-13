<?php

namespace App\Enums;

enum ActivityEventEnum: string
{
    case PROPERTY_CREATED = 'property-created';
    case PROPERTY_UPDATED = 'property-updated';
    case PROPERTY_DELETED = 'property-deleted';
    case UNIT_CREATED = 'unit-created';
    case UNIT_UPDATED = 'unit-updated';
    case UNIT_DELETED = 'unit-deleted';
    case RESIDENT_CREATED = 'resident-created';
    case TECHNICIAN_CREATED = 'technician-created';
    case MAINTENANCE_REQUEST_CREATED = 'maintenance-request-created';
    case MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED = 'maintenance-request-technician-assigned';
    case MAINTENANCE_REQUEST_STATUS_UPDATED = 'maintenance-request-status-updated';
    case MAINTENANCE_REQUEST_WORK_STARTED = 'maintenance-request-work-started';
    case MAINTENANCE_REQUEST_WORK_COMPLETED = 'maintenance-request-work-completed';
    case MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED = 'maintenance-request-resolution-confirmed';
    case MAINTENANCE_REQUEST_REOPENED = 'maintenance-request-reopened';

    public function getIcon(): string
    {
        return match ($this) {
            self::PROPERTY_CREATED => 'Building2',
            self::PROPERTY_UPDATED => 'Building2',
            self::PROPERTY_DELETED => 'Trash2',
            self::UNIT_CREATED => 'DoorOpen',
            self::UNIT_UPDATED => 'DoorOpen',
            self::UNIT_DELETED => 'Trash2',
            self::RESIDENT_CREATED => 'UserRoundPlus',
            self::TECHNICIAN_CREATED => 'Wrench',
            self::MAINTENANCE_REQUEST_CREATED => 'ClipboardPlus',
            self::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED => 'UserRoundCog',
            self::MAINTENANCE_REQUEST_STATUS_UPDATED => 'RefreshCw',
            self::MAINTENANCE_REQUEST_WORK_STARTED => 'Play',
            self::MAINTENANCE_REQUEST_WORK_COMPLETED => 'CircleCheckBig',
            self::MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED => 'BadgeCheck',
            self::MAINTENANCE_REQUEST_REOPENED => 'RotateCcw',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PROPERTY_CREATED,
            self::PROPERTY_UPDATED,
            self::PROPERTY_DELETED => 'text-sky-700',
            self::UNIT_CREATED,
            self::UNIT_UPDATED,
            self::UNIT_DELETED => 'text-violet-700',
            self::RESIDENT_CREATED,
            self::MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED => 'text-emerald-700',
            self::TECHNICIAN_CREATED,
            self::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED => 'text-amber-700',
            self::MAINTENANCE_REQUEST_CREATED => 'text-orange-700',
            self::MAINTENANCE_REQUEST_STATUS_UPDATED => 'text-indigo-700',
            self::MAINTENANCE_REQUEST_WORK_STARTED => 'text-cyan-700',
            self::MAINTENANCE_REQUEST_WORK_COMPLETED => 'text-green-700',
            self::MAINTENANCE_REQUEST_REOPENED => 'text-rose-700',
        };
    }

    public function getBgColor(): string
    {
        return match ($this) {
            self::PROPERTY_CREATED,
            self::PROPERTY_UPDATED,
            self::PROPERTY_DELETED => 'bg-sky-100',
            self::UNIT_CREATED,
            self::UNIT_UPDATED,
            self::UNIT_DELETED => 'bg-violet-100',
            self::RESIDENT_CREATED,
            self::MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED => 'bg-emerald-100',
            self::TECHNICIAN_CREATED,
            self::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED => 'bg-amber-100',
            self::MAINTENANCE_REQUEST_CREATED => 'bg-orange-100',
            self::MAINTENANCE_REQUEST_STATUS_UPDATED => 'bg-indigo-100',
            self::MAINTENANCE_REQUEST_WORK_STARTED => 'bg-cyan-100',
            self::MAINTENANCE_REQUEST_WORK_COMPLETED => 'bg-green-100',
            self::MAINTENANCE_REQUEST_REOPENED => 'bg-rose-100',
        };
    }
}
