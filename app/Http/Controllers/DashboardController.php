<?php

namespace App\Http\Controllers;

use App\Actions\Organization\Dashboard\GetTotalProperties;
use App\Actions\Organization\Dashboard\GetTotalUnits;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('organization/dashboard/index', [
            'totalProperties' => Inertia::defer(
                fn (): int => GetTotalProperties::handle(),
                'totalProperties',
            ),
            'totalUnits' => Inertia::defer(
                fn (): int => GetTotalUnits::handle(),
                'totalUnits',
            ),
        ]);
    }
}
