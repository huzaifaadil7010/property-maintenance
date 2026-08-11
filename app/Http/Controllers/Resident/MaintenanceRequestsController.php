<?php

namespace App\Http\Controllers\Resident;

use App\Actions\Resident\GetMyMaintenanceRequests;
use App\Data\MaintenanceRequestFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentMaintenanceRequestResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        ]);
    }
}
