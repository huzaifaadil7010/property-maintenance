<?php

namespace App\Actions\Organization\Common;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetAvailableTechniciansForDropdown
{
    public static function handle(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->technician()
            ->whereRelation('technicianProfiles', 'is_available', true)
            ->get();
    }
}
