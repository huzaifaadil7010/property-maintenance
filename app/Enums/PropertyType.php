<?php

namespace App\Enums;

use App\Concerns\EnumHelper;

enum PropertyType: string
{
    use EnumHelper;

    case APARTMENT = 'apartment';

    public function getLabel(): string
    {
        return match ($this) {
            self::APARTMENT => 'Apartment',
        };
    }
}
