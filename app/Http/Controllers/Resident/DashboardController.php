<?php

namespace App\Http\Controllers\Resident;

use App\Actions\Resident\Dashboard\GetCurrentResidence;
use App\Actions\Resident\Dashboard\GetRecentMaintenanceRequests;
use App\Actions\Resident\Dashboard\GetTotalMaintenanceRequestsByStatus;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentMaintenanceRequestResource;
use App\Http\Resources\ResidentResidenceResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $resident = $request->user();
        $residence = GetCurrentResidence::handle($resident);

        return Inertia::render('resident/dashboard/index', [
            'residence' => $residence ? new ResidentResidenceResource($residence) : null,
            'maintenanceCategories' => Inertia::defer(
                fn (): array => MaintenanceCategory::getLabeledValues(),
                'maintenanceRequestOptions',
            )->once(),
            'maintenancePriorities' => Inertia::defer(
                fn (): array => MaintenancePriority::getLabeledValues(),
                'maintenanceRequestOptions',
            )->once(),
            'recentMaintenanceRequests' => Inertia::defer(
                fn () => ResidentMaintenanceRequestResource::collection(
                    GetRecentMaintenanceRequests::handle($resident),
                ),
                'recentMaintenanceRequests',
            ),
            'totalOpenRequests' => Inertia::defer(
                fn (): int => GetTotalMaintenanceRequestsByStatus::handle([
                    MaintenanceRequestStatus::OPEN,
                    MaintenanceRequestStatus::ASSIGNED,
                ], $resident),
                'totalOpenRequests',
            ),
            'totalInProgressRequests' => Inertia::defer(
                fn (): int => GetTotalMaintenanceRequestsByStatus::handle([
                    MaintenanceRequestStatus::IN_PROGRESS,
                    MaintenanceRequestStatus::REOPENED,
                ], $resident),
                'totalInProgressRequests',
            ),
            'totalCompletedRequests' => Inertia::defer(
                fn (): int => GetTotalMaintenanceRequestsByStatus::handle([
                    MaintenanceRequestStatus::COMPLETED,
                    MaintenanceRequestStatus::CLOSED,
                ], $resident),
                'totalCompletedRequests',
            ),
        ]);
    }
}
