<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Resident\ConfirmResidentChange;
use App\Mcp\Tools\Resident\GetResidentDashboardData;
use App\Mcp\Tools\Resident\GetResidentMaintenanceOptions;
use App\Mcp\Tools\Resident\GetResidentMaintenanceRequest;
use App\Mcp\Tools\Resident\GetResidentRequestImage;
use App\Mcp\Tools\Resident\ListResidentMaintenanceRequests;
use App\Mcp\Tools\Resident\PrepareConfirmResidentResolution;
use App\Mcp\Tools\Resident\PrepareCreateResidentMaintenanceRequest;
use App\Mcp\Tools\Resident\PrepareReopenResidentMaintenanceRequest;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tools\ToolSearch;

#[Name('Resident Server')]
#[Version('0.0.1')]
#[Instructions('Manage only the configured local resident\'s own maintenance requests. Read tools do not change data. Every final write requires a prepare tool, user review of its summary and impact, then confirm-resident-change with the one-time token. Never confirm a change without the user approving its preview. Issue images must be placed in storage/app/private/mcp/resident-inbox and referred to by filename only.')]
class ResidentServer extends Server
{
    protected array $tools = [
        ToolSearch::class => [
            GetResidentDashboardData::class,
            ListResidentMaintenanceRequests::class,
            GetResidentMaintenanceRequest::class,
            GetResidentMaintenanceOptions::class,
            GetResidentRequestImage::class,
            PrepareCreateResidentMaintenanceRequest::class,
            PrepareConfirmResidentResolution::class,
            PrepareReopenResidentMaintenanceRequest::class,
            ConfirmResidentChange::class,
        ],
    ];
}
