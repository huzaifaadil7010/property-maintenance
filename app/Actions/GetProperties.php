<?php

namespace App\Actions;

use App\Data\PropertyFilterData;
use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProperties
{
    public static function handle(PropertyFilterData $filters): LengthAwarePaginator
    {
        return Property::query()
            ->latest('id')
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }
}
