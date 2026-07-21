<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetProperties;
use App\Actions\Organization\Property\CreateProperty;
use App\Actions\Organization\Property\UpdateProperty;
use App\Data\PropertyData;
use App\Data\PropertyFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertiesRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Organization;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PropertiesController extends Controller
{
    public function index(Request $request): Response
    {
        $properties = GetProperties::handle(PropertyFilterData::from($request->all()));

        return Inertia::render('organization/property/index', [
            'properties' => PropertyResource::collection($properties),
        ]);
    }

    public function store(PropertiesRequest $request): RedirectResponse
    {
        $data = PropertyData::from($request->validated());

        CreateProperty::handle($data);

        return Inertia::flash('success', 'Property created successfully.')->back();
    }

    public function update(
        PropertiesRequest $request,
        Organization $organization,
        Property $property,
    ): RedirectResponse {
        $data = PropertyData::from($request->validated());

        UpdateProperty::handle($property, $data);

        return Inertia::flash('success', 'Property updated successfully.')->back();
    }

    public function destroy(Organization $organization, Property $property): RedirectResponse
    {
        $property->delete();

        return Inertia::flash('success', 'Property deleted successfully.')->back();
    }
}
