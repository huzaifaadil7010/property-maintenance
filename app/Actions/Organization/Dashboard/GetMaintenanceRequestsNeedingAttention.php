<?php

namespace App\Actions\Organization\Dashboard;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Collection;

class GetMaintenanceRequestsNeedingAttention
{
    public static function handle(): Collection
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
            ->whereIn('status', [
                MaintenanceRequestStatus::OPEN,
                MaintenanceRequestStatus::REOPENED,
            ])
            ->with(['property:id,name', 'unit:id,name'])
            ->latest('id')
            ->limit(5)
            ->get();
    }
}
