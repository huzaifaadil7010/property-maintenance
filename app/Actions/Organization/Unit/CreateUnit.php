<?php

namespace App\Actions\Organization\Unit;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\UnitData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;

class CreateUnit
{
    public static function handle(
        UnitData $data,
        User $actor,
        Organization $organization,
    ): Unit {
        $unit = Unit::create($data->toArray());

        self::logActivity($unit, $actor, $organization);

        return $unit;
    }

    private static function logActivity(
        Unit $unit,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::UNIT_CREATED,
            'title' => 'Unit created',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':unit' => $unit->name,
            ], ':actor created unit ":unit".'),
            'subject' => $unit,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
