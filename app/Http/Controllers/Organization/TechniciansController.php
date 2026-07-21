<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetTechnicians;
use App\Data\TechnicianFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicianResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TechniciansController extends Controller
{
    public function index(Request $request): Response
    {
        $technicians = GetTechnicians::handle(TechnicianFilterData::from($request->all()));

        return Inertia::render('organization/technician/index', [
            'technicians' => TechnicianResource::collection($technicians),
        ]);
    }
}
