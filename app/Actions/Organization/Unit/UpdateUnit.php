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

class UpdateUnit
{
    public static function handle(
        Unit $unit,
        UnitData $data,
        User $actor,
        Organization $organization,
    ): bool {
        $updated = $unit->update($data->toArray());

        if ($updated) {
            self::logActivity($unit, $actor, $organization);
        }

        return $updated;
    }

    private static function logActivity(
        Unit $unit,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::UNIT_UPDATED,
            'title' => 'Unit updated',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':unit' => $unit->name,
            ], ':actor updated unit ":unit".'),
            'subject' => $unit,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
