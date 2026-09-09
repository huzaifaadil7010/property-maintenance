<?php

namespace App\Actions\Technician;

use App\Data\MaintenanceRequestFilterData;
use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMyJobs
{
    public static function handle(
        MaintenanceRequestFilterData $filters,
        User $technician,
    ): LengthAwarePaginator {
        return MaintenanceRequest::query()
            ->select([
                'id',
                'property_id',
                'unit_id',
                'title',
                'description',
                'category',
                'priority',
                'status',
                'created_at',
            ])
            ->where('assigned_technician_id', $technician->id)
            ->with(['property:id,name', 'unit:id,name'])
            ->latest('id')
            ->when(
                $filters->status,
                function (Builder $query, MaintenanceRequestStatus $status): void {
                    self::filterByStatus($query, $status);
                },
            )
            ->when($filters->search, fn (Builder $query, string $search): Builder => $query->where(
                fn (Builder $query): Builder => $query
                    ->whereAny(['title', 'description', 'category', 'priority', 'status'], 'like', "%{$search}%")
                    ->orWhereHas('property', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('unit', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%")),
            ))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }

    private static function filterByStatus(Builder $query, MaintenanceRequestStatus $status): void
    {
        match ($status) {
            MaintenanceRequestStatus::ASSIGNED => $query->assigned(),
            MaintenanceRequestStatus::IN_PROGRESS => $query->inProgress(),
            MaintenanceRequestStatus::COMPLETED => $query->completed(),
            default => null,
        };
    }
}
