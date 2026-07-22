<?php

namespace App\Actions\Organization;

use App\Data\MaintenanceRequestFilterData;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMaintenanceRequests
{
    public static function handle(MaintenanceRequestFilterData $filters): LengthAwarePaginator
    {
        return MaintenanceRequest::query()
            ->select([
                'id',
                'property_id',
                'unit_id',
                'resident_id',
                'assigned_technician_id',
                'title',
                'category',
                'priority',
                'status',
                'created_at',
            ])
            ->with([
                'property:id,name',
                'unit:id,name',
                'resident:id,name',
                'assignedTechnician:id,name',
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
                ->whereAny(['title', 'description', 'category', 'priority', 'status'], 'like', "%{$search}%")
                ->orWhereHas(
                    'property',
                    fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"),
                )
                ->orWhereHas(
                    'unit',
                    fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"),
                )
                ->orWhereHas(
                    'resident',
                    fn (Builder $query): Builder => $query->whereAny(
                        ['name', 'email', 'phone'],
                        'like',
                        "%{$search}%",
                    ),
                )
                ->orWhereHas(
                    'assignedTechnician',
                    fn (Builder $query): Builder => $query->whereAny(
                        ['name', 'email', 'phone'],
                        'like',
                        "%{$search}%",
                    ),
                ),
        );
    }
}
