<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetDashboardData;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Organization Server')]
#[Version('0.0.1')]
#[Instructions('Read the current organization dashboard for the configured local owner. This server currently exposes read-only dashboard data only.')]
class OrganizationServer extends Server
{
    protected array $tools = [
        GetDashboardData::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
