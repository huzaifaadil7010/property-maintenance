<?php

namespace App\Actions\Organization\Property;

use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\PropertyData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Str;

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
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':property' => $property->name,
            ], ':actor updated property ":property".'),
            'subject' => $property,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
