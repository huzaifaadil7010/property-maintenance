<?php

namespace App\Http\Controllers\Organization;

use App\Actions\Organization\GetActivityLogs;
use App\Data\ActivityLogPaginationData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogsController extends Controller
{
    public function index(Request $request, Organization $organization): Response
    {
        return Inertia::render('organization/activity-log/index', [
            'activityLogs' => Inertia::scroll(
                fn () => ActivityLogResource::collection(
                    GetActivityLogs::handle(ActivityLogPaginationData::from([
                        'page' => $request->integer('page', 1),
                        'perPage' => 20,
                    ])),
                ),
            ),
        ]);
    }
}
