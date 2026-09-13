<?php

namespace App\Actions\Organization\Unit;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;

class DeleteUnit
{
    public static function handle(
        Unit $unit,
        User $actor,
        Organization $organization,
    ): bool {
        $deleted = (bool) $unit->delete();

        if ($deleted) {
            self::logActivity($unit, $actor, $organization);
        }

        return $deleted;
    }

    private static function logActivity(
        Unit $unit,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::UNIT_DELETED,
            'title' => 'Unit deleted',
            'description' => sprintf('%s deleted unit "%s".', $actor->name, $unit->name),
            'subject' => $unit,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
