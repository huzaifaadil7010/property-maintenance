<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetTechnicians;
use App\Actions\Organization\Technician\CreateTechnician;
use App\Data\TechnicianData;
use App\Data\TechnicianFilterData;
use App\Enums\TechnicianSpecialty;
use App\Http\Controllers\Controller;
use App\Http\Requests\TechniciansRequest;
use App\Http\Resources\TechnicianResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TechniciansController extends Controller
{
    public function index(Request $request): Response
    {
        $technicians = GetTechnicians::handle(TechnicianFilterData::from($request->all()));

        return Inertia::render('organization/technician/index', [
            'technicians' => TechnicianResource::collection($technicians),
            'technicianSpecialties' => TechnicianSpecialty::getLabeledValues(),
        ]);
    }

    public function store(TechniciansRequest $request, Organization $organization): RedirectResponse
    {
        $data = TechnicianData::from($request->validated());

        CreateTechnician::handle($data, $organization);

        return Inertia::flash('success', 'Technician created successfully.')->back();
    }
}
