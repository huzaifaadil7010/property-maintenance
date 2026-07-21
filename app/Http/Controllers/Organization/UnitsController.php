<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\Common\GetPropertiesForDropDown;
use App\Actions\Organization\GetUnits;
use App\Actions\Organization\Unit\CreateUnit;
use App\Actions\Organization\Unit\UpdateUnit;
use App\Data\UnitData;
use App\Data\UnitFilterData;
use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UnitsRequest;
use App\Http\Resources\UnitResource;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $units = GetUnits::handle(UnitFilterData::from($request->all()));

        return Inertia::render('organization/unit/index', [
            'units' => UnitResource::collection($units),
            'unitStatuses' => UnitStatus::getLabeledValues(),
            'properties' => Inertia::defer(
                fn (): Collection => GetPropertiesForDropDown::handle(),
            )->once(),
        ]);
    }

    public function store(UnitsRequest $request): RedirectResponse
    {
        $data = UnitData::from($request->validated());

        CreateUnit::handle($data);

        return Inertia::flash('success', 'Unit created successfully.')->back();
    }

    public function update(
        UnitsRequest $request,
        Organization $organization,
        Unit $unit,
    ): RedirectResponse {
        $data = UnitData::from($request->validated());

        UpdateUnit::handle($unit, $data);

        return Inertia::flash('success', 'Unit updated successfully.')->back();
    }

    public function destroy(Organization $organization, Unit $unit): RedirectResponse
    {
        $unit->delete();

        return Inertia::flash('success', 'Unit deleted successfully.')->back();
    }
}
