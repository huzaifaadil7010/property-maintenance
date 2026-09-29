<?php

namespace App\Actions\Resident;

use App\Data\MaintenanceRequestFilterData;
use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

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
            ->when($filters->status, fn (Builder $query, MaintenanceRequestStatus $status): Builder => $query->where('status', $status))
            ->when($filters->from, fn (Builder $query, string $from): Builder => $query->where('created_at', '>=', Carbon::parse($from, 'UTC')->startOfDay()))
            ->when($filters->to, fn (Builder $query, string $to): Builder => $query->where('created_at', '<', Carbon::parse($to, 'UTC')->addDay()->startOfDay()))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }
}
