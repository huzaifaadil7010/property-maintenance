<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetMaintenanceRequests;
use App\Data\MaintenanceRequestFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceRequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $maintenanceRequests = GetMaintenanceRequests::handle(
            MaintenanceRequestFilterData::from($request->all()),
        );

        return Inertia::render('organization/maintenance-request/index', [
            'maintenanceRequests' => MaintenanceRequestResource::collection($maintenanceRequests),
        ]);
    }

    public function show(Organization $organization, MaintenanceRequest $maintenanceRequest): Response
    {
        $maintenanceRequest->load([
            'property',
            'unit',
            'resident',
            'assignedTechnician',
            'attachments.uploader',
            'statusLogs.changedBy',
        ]);

        return Inertia::render('organization/maintenance-request/show', [
            'maintenanceRequest' => $maintenanceRequest->toResource(),
        ]);
    }
}
