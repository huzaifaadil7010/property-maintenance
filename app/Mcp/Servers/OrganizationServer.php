<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ConfirmOrganizationChange;
use App\Mcp\Tools\GetDashboardData;
use App\Mcp\Tools\GetOrganizationOptions;
use App\Mcp\Tools\GetOrganizationRecord;
use App\Mcp\Tools\ListOrganizationRecords;
use App\Mcp\Tools\PrepareAssignTechnician;
use App\Mcp\Tools\PrepareCreateProperty;
use App\Mcp\Tools\PrepareCreateResident;
use App\Mcp\Tools\PrepareCreateTechnician;
use App\Mcp\Tools\PrepareCreateUnit;
use App\Mcp\Tools\PrepareDeleteProperty;
use App\Mcp\Tools\PrepareDeleteUnit;
use App\Mcp\Tools\PrepareUpdateMaintenanceStatus;
use App\Mcp\Tools\PrepareUpdateProperty;
use App\Mcp\Tools\PrepareUpdateResident;
use App\Mcp\Tools\PrepareUpdateUnit;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tools\ToolSearch;

#[Name('Organization Server')]
#[Version('0.0.1')]
#[Instructions('Manage the current organization as the configured local owner. Read tools do not change data. Every write requires a prepare tool, user review of its summary and impact, then confirm-organization-change with the one-time token. Never confirm a change without the user approving its preview.')]
class OrganizationServer extends Server
{
    protected array $tools = [
        ToolSearch::class => [
            GetDashboardData::class,
            ListOrganizationRecords::class,
            GetOrganizationRecord::class,
            GetOrganizationOptions::class,
            PrepareCreateProperty::class,
            PrepareUpdateProperty::class,
            PrepareDeleteProperty::class,
            PrepareCreateUnit::class,
            PrepareUpdateUnit::class,
            PrepareDeleteUnit::class,
            PrepareCreateResident::class,
            PrepareUpdateResident::class,
            PrepareCreateTechnician::class,
            PrepareAssignTechnician::class,
            PrepareUpdateMaintenanceStatus::class,
            ConfirmOrganizationChange::class,
        ],
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
