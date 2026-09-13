<?php

namespace App\Actions\Organization\Property;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;

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
            'title' => __('Property deleted'),
            'description' => __(':actor deleted property ":property".', [
                'actor' => $actor->name,
                'property' => $property->name,
            ]),
            'subject' => $property,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
