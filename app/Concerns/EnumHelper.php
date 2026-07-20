<?php

namespace App\Concerns;

use BackedEnum;

trait EnumHelper
{
    public static function getLabeledValues(): array
    {
        return array_map(
            fn (BackedEnum $case): array => [
                'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->value,
                'value' => $case->value,
            ],
            self::cases(),
        );
    }
}
