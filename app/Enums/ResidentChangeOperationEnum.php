<?php

namespace App\Enums;

use App\Data\ConfirmResidentResolutionChangeData;
use App\Data\CreateResidentMaintenanceRequestChangeData;
use App\Data\ReopenResidentMaintenanceRequestChangeData;
use App\Data\ResidentChangeInputData;

enum ResidentChangeOperationEnum: string
{
    case CREATE_MAINTENANCE_REQUEST = 'create-maintenance-request';
    case CONFIRM_RESOLUTION = 'confirm-resolution';
    case REOPEN_MAINTENANCE_REQUEST = 'reopen-maintenance-request';

    public function inputDataClass(): string
    {
        return match ($this) {
            self::CREATE_MAINTENANCE_REQUEST => CreateResidentMaintenanceRequestChangeData::class,
            self::CONFIRM_RESOLUTION => ConfirmResidentResolutionChangeData::class,
            self::REOPEN_MAINTENANCE_REQUEST => ReopenResidentMaintenanceRequestChangeData::class,
        };
    }

    public function accepts(ResidentChangeInputData $input): bool
    {
        $inputDataClass = $this->inputDataClass();

        return $input instanceof $inputDataClass;
    }
}
