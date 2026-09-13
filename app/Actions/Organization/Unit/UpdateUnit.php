<?php

namespace App\Actions\Organization\Unit;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\UnitData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;

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
            'description' => sprintf('%s updated unit "%s".', $actor->name, $unit->name),
            'subject' => $unit,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
