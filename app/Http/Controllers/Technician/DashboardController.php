<?php

namespace App\Http\Controllers\Technician;

use App\Actions\Technician\Dashboard\GetCurrentActiveJobs;
use App\Actions\Technician\Dashboard\GetTotalJobsByStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicianMaintenanceRequestResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $technician = $request->user();

        return Inertia::render('technician/dashboard/index', [
            'currentActiveJobs' => Inertia::defer(
                fn () => TechnicianMaintenanceRequestResource::collection(
                    GetCurrentActiveJobs::handle($technician),
                ),
                'currentActiveJobs',
            ),
            'totalAssignedJobs' => Inertia::defer(
                fn (): int => GetTotalJobsByStatus::handle(
                    MaintenanceRequestStatus::ASSIGNED,
                    $technician,
                ),
                'totalAssignedJobs',
            ),
            'totalCompletedJobs' => Inertia::defer(
                fn (): int => GetTotalJobsByStatus::handle(
                    MaintenanceRequestStatus::COMPLETED,
                    $technician,
                ),
                'totalCompletedJobs',
            ),
            'totalInProgressJobs' => Inertia::defer(
                fn (): int => GetTotalJobsByStatus::handle(
                    MaintenanceRequestStatus::IN_PROGRESS,
                    $technician,
                ),
                'totalInProgressJobs',
            ),
        ]);
    }
}
