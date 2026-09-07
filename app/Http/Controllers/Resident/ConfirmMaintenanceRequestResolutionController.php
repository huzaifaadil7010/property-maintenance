<?php

namespace App\Http\Controllers\Resident;

use App\Actions\Resident\MaintenanceRequest\ConfirmMaintenanceRequestResolution;
use App\Data\MaintenanceRequestStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmMaintenanceRequestResolutionRequest;
use App\Models\MaintenanceRequest;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfirmMaintenanceRequestResolutionController extends Controller
{
    public function __invoke(ConfirmMaintenanceRequestResolutionRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        ConfirmMaintenanceRequestResolution::handle($maintenanceRequest, MaintenanceRequestStatusData::from($request->validated()), $request->user());

        return Inertia::flash('success', 'Request resolution confirmed.')->back();
    }
}
