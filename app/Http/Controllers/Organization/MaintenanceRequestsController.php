<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetMaintenanceRequests;
use App\Data\MaintenanceRequestFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceRequestResource;
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
}
