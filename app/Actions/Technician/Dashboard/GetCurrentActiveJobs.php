<?php

namespace App\Actions\Technician\Dashboard;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetCurrentActiveJobs
{
    public static function handle(User $technician): Collection
    {
        return MaintenanceRequest::query()
            ->select([
                'id',
                'property_id',
                'unit_id',
                'title',
                'category',
                'priority',
                'status',
                'created_at',
            ])
            ->whereBelongsTo($technician, 'assignedTechnician')
            ->whereIn('status', [
                MaintenanceRequestStatus::ASSIGNED,
                MaintenanceRequestStatus::IN_PROGRESS,
            ])
            ->with(['property:id,name', 'unit:id,name'])
            ->latest('id')
            ->limit(5)
            ->get();
    }
}
