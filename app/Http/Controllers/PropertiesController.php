<?php

namespace App\Http\Controllers;

use App\Actions\GetProperties;
use App\Http\Resources\PropertyResource;
use Inertia\Inertia;
use Inertia\Response;

class PropertiesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('organization/property/index', [
            'properties' => PropertyResource::collection(GetProperties::handle()),
        ]);
    }
}
