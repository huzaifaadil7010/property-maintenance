<?php

namespace App\Mcp\Tools;

use App\Actions\Organization\Dashboard\GetDashboardData as GetOrganizationDashboardData;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetDashboardData extends Tool
{
    protected string $description = 'Get the configured owner\'s current organization dashboard totals, maintenance requests needing attention, and recent activity. This tool does not change data.';

    public function handle(Request $request): ResponseFactory
    {
        return Response::structured(GetOrganizationDashboardData::handle($request->user()->currentOrganization));
    }
}
