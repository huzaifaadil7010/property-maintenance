<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PropertiesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('organization/property/index');
    }
}
