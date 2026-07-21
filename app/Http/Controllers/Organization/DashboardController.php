<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\Common\GetPropertiesForDropDown;
use App\Actions\Organization\Dashboard\GetTotalActiveResidents;
use App\Actions\Organization\Dashboard\GetTotalAvailableTechnicians;
use App\Actions\Organization\Dashboard\GetTotalOccupiedUnits;
use App\Actions\Organization\Dashboard\GetTotalProperties;
use App\Actions\Organization\Dashboard\GetTotalUnits;
use App\Actions\Organization\Dashboard\GetTotalVacantUnits;
use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('organization/dashboard/index', [
            'properties' => Inertia::defer(
                fn (): Collection => GetPropertiesForDropDown::handle(),
            )->once(),
            'unitStatuses' => UnitStatus::getLabeledValues(),
            'totalActiveResidents' => Inertia::defer(
                fn (): int => GetTotalActiveResidents::handle(),
                'totalActiveResidents',
            ),
            'totalAvailableTechnicians' => Inertia::defer(
                fn (): int => GetTotalAvailableTechnicians::handle(),
                'totalAvailableTechnicians',
            ),
            'totalOccupiedUnits' => Inertia::defer(
                fn (): int => GetTotalOccupiedUnits::handle(),
                'totalOccupiedUnits',
            ),
            'totalProperties' => Inertia::defer(
                fn (): int => GetTotalProperties::handle(),
                'totalProperties',
            ),
            'totalUnits' => Inertia::defer(
                fn (): int => GetTotalUnits::handle(),
                'totalUnits',
            ),
            'totalVacantUnits' => Inertia::defer(
                fn (): int => GetTotalVacantUnits::handle(),
                'totalVacantUnits',
            ),
        ]);
    }
}
