<?php

namespace App\Http\Controllers\Resident;

use App\Actions\Resident\MaintenanceRequest\ReopenMaintenanceRequest;
use App\Data\MaintenanceRequestStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReopenMaintenanceRequestRequest;
use App\Models\MaintenanceRequest;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReopenMaintenanceRequestController extends Controller
{
    public function __invoke(ReopenMaintenanceRequestRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        ReopenMaintenanceRequest::handle($maintenanceRequest, MaintenanceRequestStatusData::from($request->validated()), $request->user());

        return Inertia::flash('success', 'Request reopened successfully.')->back();
    }
}
