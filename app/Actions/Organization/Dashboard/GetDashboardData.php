<?php

namespace App\Actions\Organization\Dashboard;

use App\Actions\Organization\GetActivityLogs;
use App\Data\ActivityLogPaginationData;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\OrganizationDashboardMaintenanceRequestResource;
use App\Models\Organization;

class GetDashboardData
{
    public static function handle(Organization $organization): array
    {
        $requests = GetMaintenanceRequestsNeedingAttention::handle()
            ->map(fn ($request): array => (new OrganizationDashboardMaintenanceRequestResource($request))->resolve())
            ->all();

        $activityLogs = GetActivityLogs::handle(ActivityLogPaginationData::from([
            'page' => 1,
            'perPage' => 6,
        ]))->getCollection()
            ->map(fn ($log): array => (new ActivityLogResource($log))->resolve())
            ->all();

        return [
            'organization' => [
                'id' => $organization->id,
                'uuid' => $organization->uuid,
                'name' => $organization->name,
            ],
            'totalProperties' => GetTotalProperties::handle(),
            'totalUnits' => GetTotalUnits::handle(),
            'totalVacantUnits' => GetTotalVacantUnits::handle(),
            'totalOccupiedUnits' => GetTotalOccupiedUnits::handle(),
            'totalActiveResidents' => GetTotalActiveResidents::handle(),
            'totalAvailableTechnicians' => GetTotalAvailableTechnicians::handle(),
            'totalOpenRequests' => GetTotalMaintenanceRequestsByStatus::handle([
                MaintenanceRequestStatus::OPEN,
                MaintenanceRequestStatus::ASSIGNED,
            ]),
            'totalInProgressRequests' => GetTotalMaintenanceRequestsByStatus::handle([
                MaintenanceRequestStatus::IN_PROGRESS,
                MaintenanceRequestStatus::REOPENED,
            ]),
            'totalCompletedRequests' => GetTotalMaintenanceRequestsByStatus::handle([
                MaintenanceRequestStatus::COMPLETED,
                MaintenanceRequestStatus::CLOSED,
            ]),
            'maintenanceRequestsNeedingAttention' => $requests,
            'recentActivityLogs' => $activityLogs,
        ];
    }
}
