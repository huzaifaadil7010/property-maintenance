<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\MaintenanceRequest\AssignMaintenanceRequestTechnician;
use App\Data\AssignMaintenanceRequestTechnicianData;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignMaintenanceRequestTechnicianRequest;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AssignMaintenanceRequestTechnicianController extends Controller
{
    public function __invoke(
        AssignMaintenanceRequestTechnicianRequest $request,
        Organization $organization,
        MaintenanceRequest $maintenanceRequest,
    ): RedirectResponse {
        $status = $maintenanceRequest->assigned_technician_id === null
            ? MaintenanceRequestStatus::ASSIGNED
            : $maintenanceRequest->status;

        $data = AssignMaintenanceRequestTechnicianData::from([
            ...$request->validated(),
            'status' => $status,
        ]);

        AssignMaintenanceRequestTechnician::handle($maintenanceRequest, $data, Auth::user());

        return Inertia::flash('success', 'Technician assigned successfully.')->back();
    }
}
