<?php

namespace App\Actions\Organization;

use App\Data\UnitFilterData;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetUnits
{
    public static function handle(UnitFilterData $filters): LengthAwarePaginator
    {
        return Unit::query()
            ->with('property:id,name')
            ->latest('id')
            ->when($filters->search, fn ($query, $search) => self::filterBySearch($query, $search))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }

    private static function filterBySearch(Builder $query, string $search): void
    {
        $query->where(
            fn (Builder $query): Builder => $query
                ->whereAny(['name', 'floor', 'status'], 'like', "%{$search}%")
                ->orWhereHas(
                    'property',
                    fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"),
                ),
        );
    }
}
