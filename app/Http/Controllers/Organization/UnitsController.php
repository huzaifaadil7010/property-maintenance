<?php

namespace App\Http\Controllers\Organization;

use App\Actions\GetUnits;
use App\Data\UnitFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $units = GetUnits::handle(UnitFilterData::from($request->all()));

        return Inertia::render('organization/unit/index', [
            'units' => UnitResource::collection($units),
        ]);
    }
}
