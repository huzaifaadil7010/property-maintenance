<?php

namespace App\Actions\Organization;

use App\Data\ResidentFilterData;
use App\Enums\OccupancyStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

class GetResidents
{
    public static function handle(ResidentFilterData $filters): LengthAwarePaginator
    {
        return User::query()
            ->select(['id', 'name', 'email', 'phone', 'created_at'])
            ->role(UserRole::RESIDENT)
            ->with([
                'occupancies' => fn (HasMany $query): HasMany => $query
                    ->select(['id', 'resident_id', 'unit_id', 'starts_at'])
                    ->where('status', OccupancyStatus::ACTIVE)
                    ->latest('starts_at')
                    ->with([
                        'unit:id,property_id,name',
                        'unit.property:id,name',
                    ]),
            ])
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
                ->whereAny(['name', 'email', 'phone'], 'like', "%{$search}%")
                ->orWhereHas(
                    'occupancies',
                    fn (Builder $query): Builder => $query
                        ->where('status', OccupancyStatus::ACTIVE)
                        ->whereHas(
                            'unit',
                            fn (Builder $query): Builder => $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'property',
                                    fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"),
                                ),
                        ),
                ),
        );
    }
}
