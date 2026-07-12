<?php

namespace App\Enums;

enum PropertyType: string
{
    case APARTMENT = 'apartment';

    public function getLabel(): string
    {
        return match ($this) {
            self::APARTMENT => 'Apartment',
        };
    }
}
