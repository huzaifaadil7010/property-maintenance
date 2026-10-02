<?php

namespace App\Enums;

use App\Data\AssignTechnicianChangeData;
use App\Data\CreatePropertyChangeData;
use App\Data\CreateResidentChangeData;
use App\Data\CreateTechnicianChangeData;
use App\Data\CreateUnitChangeData;
use App\Data\DeletePropertyChangeData;
use App\Data\DeleteUnitChangeData;
use App\Data\OrganizationChangeInputData;
use App\Data\UpdateMaintenanceStatusChangeData;
use App\Data\UpdatePropertyChangeData;
use App\Data\UpdateResidentChangeData;
use App\Data\UpdateUnitChangeData;

enum OrganizationChangeOperationEnum: string
{
    case CREATE_PROPERTY = 'create-property';
    case UPDATE_PROPERTY = 'update-property';
    case DELETE_PROPERTY = 'delete-property';
    case CREATE_UNIT = 'create-unit';
    case UPDATE_UNIT = 'update-unit';
    case DELETE_UNIT = 'delete-unit';
    case CREATE_RESIDENT = 'create-resident';
    case UPDATE_RESIDENT = 'update-resident';
    case CREATE_TECHNICIAN = 'create-technician';
    case ASSIGN_TECHNICIAN = 'assign-technician';
    case UPDATE_MAINTENANCE_STATUS = 'update-maintenance-status';

    public function inputDataClass(): string
    {
        return match ($this) {
            self::CREATE_PROPERTY => CreatePropertyChangeData::class,
            self::UPDATE_PROPERTY => UpdatePropertyChangeData::class,
            self::DELETE_PROPERTY => DeletePropertyChangeData::class,
            self::CREATE_UNIT => CreateUnitChangeData::class,
            self::UPDATE_UNIT => UpdateUnitChangeData::class,
            self::DELETE_UNIT => DeleteUnitChangeData::class,
            self::CREATE_RESIDENT => CreateResidentChangeData::class,
            self::UPDATE_RESIDENT => UpdateResidentChangeData::class,
            self::CREATE_TECHNICIAN => CreateTechnicianChangeData::class,
            self::ASSIGN_TECHNICIAN => AssignTechnicianChangeData::class,
            self::UPDATE_MAINTENANCE_STATUS => UpdateMaintenanceStatusChangeData::class,
        };
    }

    public function accepts(OrganizationChangeInputData $input): bool
    {
        $inputDataClass = $this->inputDataClass();

        return $input instanceof $inputDataClass;
    }
}
