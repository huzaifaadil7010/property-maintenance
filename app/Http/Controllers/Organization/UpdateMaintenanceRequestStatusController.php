<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\MaintenanceRequest\UpdateMaintenanceRequestStatus;
use App\Data\MaintenanceRequestStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMaintenanceRequestStatusRequest;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UpdateMaintenanceRequestStatusController extends Controller
{
    public function __invoke(
        UpdateMaintenanceRequestStatusRequest $request,
        Organization $organization,
        MaintenanceRequest $maintenanceRequest,
    ): RedirectResponse {
        $data = MaintenanceRequestStatusData::from($request->validated());

        UpdateMaintenanceRequestStatus::handle($maintenanceRequest, $data, Auth::user());

        return Inertia::flash('success', 'Status updated successfully.')->back();
    }
}
