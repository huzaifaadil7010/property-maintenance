<?php

namespace App\Http\Controllers\Technician;

use App\Actions\Technician\MaintenanceRequest\StartMaintenanceRequestWork;
use App\Data\MaintenanceRequestStatusData;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartMaintenanceRequestWorkRequest;
use App\Models\MaintenanceRequest;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StartMaintenanceRequestWorkController extends Controller
{
    public function __invoke(StartMaintenanceRequestWorkRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        StartMaintenanceRequestWork::handle(
            $maintenanceRequest,
            MaintenanceRequestStatusData::from(['status' => MaintenanceRequestStatus::IN_PROGRESS]),
            $request->user(),
        );

        return Inertia::flash('success', 'Work started successfully.')->back();
    }
}
