<?php

namespace App\Actions\Resident\Dashboard;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetRecentMaintenanceRequests
{
    public static function handle(User $resident): Collection
    {
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
            ->whereBelongsTo($resident, 'resident')
            ->with('unit:id,name')
            ->latest('id')
            ->limit(5)
            ->get();
    }
}
