<?php

namespace App\Actions\Organization\Common;

use App\Enums\OccupancyStatus;
use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;

class GetPropertiesForResidentDropdown
{
    public static function handle(): Collection
    {
        return Property::query()
            ->select(['id', 'name', 'type', 'address', 'city', 'created_at'])
            ->withCount('units')
            ->withCount([
                'units as available_units_count' => fn ($query) => $query
                    ->whereDoesntHave('occupancies', fn ($query) => $query->where('status', OccupancyStatus::ACTIVE)),
            ])
            ->orderBy('name')
            ->get();
    }
}
