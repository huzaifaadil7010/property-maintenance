<?php

namespace App\Http\Controllers\Resident;

use App\Actions\Resident\CreateMaintenanceRequest;
use App\Actions\Resident\GetMyMaintenanceRequests;
use App\Data\MaintenanceRequestData;
use App\Data\MaintenanceRequestFilterData;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMaintenanceRequestRequest;
use App\Http\Resources\ResidentMaintenanceRequestResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MaintenanceRequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $myRequests = GetMyMaintenanceRequests::handle(
            MaintenanceRequestFilterData::from($request->all()),
            $request->user(),
        );

        return Inertia::render('resident/maintenance-request/index', [
            'myRequests' => ResidentMaintenanceRequestResource::collection($myRequests),
            'maintenanceCategories' => MaintenanceCategory::getLabeledValues(),
            'maintenancePriorities' => MaintenancePriority::getLabeledValues(),
        ]);
    }

    public function store(CreateMaintenanceRequestRequest $request): RedirectResponse
    {
        CreateMaintenanceRequest::handle(
            MaintenanceRequestData::from($request->validated()),
            $request->user(),
        );

        return Inertia::flash('success', 'Maintenance request reported successfully.')->back();
    }
}
