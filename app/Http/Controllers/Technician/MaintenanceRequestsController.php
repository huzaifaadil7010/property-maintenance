<?php

namespace App\Http\Controllers\Technician;

use App\Actions\Technician\GetMyJobs;
use App\Actions\Technician\MaintenanceRequest\GetMyJob;
use App\Data\MaintenanceRequestFilterData;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicianMaintenanceRequestDetailResource;
use App\Http\Resources\TechnicianMaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceRequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $myJobs = GetMyJobs::handle(
            MaintenanceRequestFilterData::from($request->all()),
            $request->user(),
        );

        return Inertia::render('technician/maintenance-request/index', [
            'myJobs' => TechnicianMaintenanceRequestResource::collection($myJobs),
            'jobStatuses' => array_values(array_filter(
                MaintenanceRequestStatus::getLabeledValues(),
                fn (array $status): bool => in_array($status['value'], [
                    MaintenanceRequestStatus::ASSIGNED->value,
                    MaintenanceRequestStatus::IN_PROGRESS->value,
                    MaintenanceRequestStatus::COMPLETED->value,
                ], true),
            )),
        ]);
    }

    public function show(Request $request, MaintenanceRequest $maintenanceRequest): Response
    {
        return Inertia::render('technician/maintenance-request/show', [
            'maintenanceRequest' => new TechnicianMaintenanceRequestDetailResource(
                GetMyJob::handle($maintenanceRequest, $request->user()),
            ),
        ]);
    }
}
