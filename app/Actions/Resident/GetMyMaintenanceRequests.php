<?php

namespace App\Actions\Resident;

use App\Data\MaintenanceRequestFilterData;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMyMaintenanceRequests
{
    public static function handle(
        MaintenanceRequestFilterData $filters,
        User $resident,
    ): LengthAwarePaginator {
        return MaintenanceRequest::query()
            ->select([
                'id',
                'unit_id',
                'title',
                'description',
                'category',
                'priority',
                'status',
                'created_at',
            ])
            ->where('resident_id', $resident->id)
            ->with('unit:id,name')
            ->latest('id')
            ->when($filters->search, fn (Builder $query, string $search): Builder => $query->where(
                fn (Builder $query): Builder => $query
                    ->whereAny(['title', 'description', 'category', 'priority', 'status'], 'like', "%{$search}%")
                    ->orWhereHas('unit', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%")),
            ))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }
}
