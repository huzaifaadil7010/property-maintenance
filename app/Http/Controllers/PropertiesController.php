<?php

namespace App\Http\Controllers;

use App\Actions\GetProperties;
use App\Data\PropertyFilterData;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertiesController extends Controller
{
    public function index(Request $request): Response
    {
        $properties = GetProperties::handle(PropertyFilterData::from($request->all()));

        return Inertia::render('organization/property/index', [
            'properties' => PropertyResource::collection($properties),
        ]);
    }
}
