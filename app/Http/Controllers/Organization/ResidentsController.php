<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\Common\GetAvailableUnitsForResidentDropdown;
use App\Actions\Organization\Common\GetPropertiesForResidentDropdown;
use App\Actions\Organization\GetResidents;
use App\Actions\Organization\Resident\CreateResident;
use App\Actions\Organization\Resident\UpdateResident;
use App\Data\ResidentData;
use App\Data\ResidentFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentsRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\ResidentResource;
use App\Http\Resources\UnitResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResidentsController extends Controller
{
    public function index(Request $request): Response
    {
        $residents = GetResidents::handle(ResidentFilterData::from($request->all()));

        return Inertia::render('organization/resident/index', [
            'residents' => ResidentResource::collection($residents),
            'residentCreateOptions' => Inertia::defer(
                fn (): array => [
                    'properties' => PropertyResource::collection(
                        GetPropertiesForResidentDropdown::handle(),
                    ),
                    'availableUnits' => UnitResource::collection(
                        GetAvailableUnitsForResidentDropdown::handle(),
                    ),
                ],
                'resident-create',
            ),
        ]);
    }

    public function store(ResidentsRequest $request, Organization $organization): RedirectResponse
    {
        $data = ResidentData::from($request->validated());

        CreateResident::handle($data, $organization, $request->user());

        return Inertia::flash('success', 'Resident created successfully.')->back();
    }

    public function update(UpdateResidentRequest $request, Organization $organization, User $resident): RedirectResponse
    {
        UpdateResident::handle(ResidentData::from($request->validated()), $resident, $request->user(), $organization);

        return Inertia::flash('success', 'Resident updated successfully.')->back();
    }
}
