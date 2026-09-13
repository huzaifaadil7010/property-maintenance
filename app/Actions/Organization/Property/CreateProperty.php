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

class CreateProperty
{
    public static function handle(
        PropertyData $data,
        User $actor,
        Organization $organization,
    ): Property {
        $property = Property::create($data->toArray());

        self::logActivity($property, $actor, $organization);

        return $property;
    }

    private static function logActivity(
        Property $property,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::PROPERTY_CREATED,
            'title' => 'Property created',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':property' => $property->name,
            ], ':actor created property ":property".'),
            'subject' => $property,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
