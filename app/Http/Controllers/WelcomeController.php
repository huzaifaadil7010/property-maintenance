<?php

namespace App\Http\Controllers;

use App\Actions\Billing\GetActivePlans;
use App\Http\Resources\PlanResource;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('welcome', [
            'plans' => PlanResource::collection(GetActivePlans::handle()),
        ]);
    }
}
