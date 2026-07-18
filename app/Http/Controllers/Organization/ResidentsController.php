<?php

namespace App\Http\Controllers\Organization;

use App\Actions\GetResidents;
use App\Data\ResidentFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResidentResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResidentsController extends Controller
{
    public function index(Request $request): Response
    {
        $residents = GetResidents::handle(ResidentFilterData::from($request->all()));

        return Inertia::render('organization/resident/index', [
            'residents' => ResidentResource::collection($residents),
        ]);
    }
}
