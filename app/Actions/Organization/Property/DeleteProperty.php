<?php

namespace App\Actions\Organization\Property;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Str;

class DeleteProperty
{
    public static function handle(
        Property $property,
        User $actor,
        Organization $organization,
    ): bool {
        $deleted = (bool) $property->delete();

        if ($deleted) {
            self::logActivity($property, $actor, $organization);
        }

        return $deleted;
    }

    private static function logActivity(
        Property $property,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::PROPERTY_DELETED,
            'title' => 'Property deleted',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':property' => $property->name,
            ], ':actor deleted property ":property".'),
            'subject' => $property,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
