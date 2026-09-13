<?php

namespace App\Actions\Organization\Property;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\PropertyData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;

class UpdateProperty
{
    public static function handle(
        Property $property,
        PropertyData $data,
        User $actor,
        Organization $organization,
    ): bool {
        $updated = $property->update($data->toArray());

        if ($updated) {
            self::logActivity($property, $actor, $organization);
        }

        return $updated;
    }

    private static function logActivity(
        Property $property,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::PROPERTY_UPDATED,
            'title' => 'Property updated',
            'description' => sprintf('%s updated property "%s".', $actor->name, $property->name),
            'subject' => $property,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
