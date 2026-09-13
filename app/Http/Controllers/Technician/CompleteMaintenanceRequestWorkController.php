<?php

namespace App\Http\Controllers\Technician;

use App\Actions\Technician\MaintenanceRequest\CompleteMaintenanceRequestWork;
use App\Data\CompleteMaintenanceRequestData;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteMaintenanceRequestWorkRequest;
use App\Models\MaintenanceRequest;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CompleteMaintenanceRequestWorkController extends Controller
{
    public function __invoke(CompleteMaintenanceRequestWorkRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        CompleteMaintenanceRequestWork::handle(
            $maintenanceRequest,
            CompleteMaintenanceRequestData::from([
                ...$request->validated(),
                'status' => MaintenanceRequestStatus::COMPLETED,
            ]),
            $request->user(),
        );

        return Inertia::flash('success', 'Work completed successfully.')->back();
    }
}
