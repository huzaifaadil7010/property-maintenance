<?php

namespace App\Actions\Organization\Common;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;

class GetAvailableUnitsForResidentDropdown
{
    public static function handle(): Collection
    {
        return Unit::query()
            ->select(['id', 'property_id', 'name', 'floor', 'status'])
            ->with(['property:id,name'])
            ->whereDoesntHave('occupancies', fn ($query) => $query->active())
            ->orderBy('name')
            ->get();
    }
}
