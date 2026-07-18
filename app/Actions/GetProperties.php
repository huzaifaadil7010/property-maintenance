<?php

namespace App\Actions;

use App\Data\PropertyFilterData;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProperties
{
    public static function handle(PropertyFilterData $filters): LengthAwarePaginator
    {
        return Property::query()
            ->latest('id')
            ->withCount('units')
            ->when($filters->search, fn ($query, $search) => self::filterBySearch($query, $search))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }

    private static function filterBySearch(Builder $query, string $search): void
    {
        $query->whereAny(['name', 'type', 'address', 'city'], 'like', "%{$search}%")
            ->orHas('units', '=', $search);
    }
}
