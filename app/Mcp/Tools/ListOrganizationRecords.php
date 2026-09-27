<?php

namespace App\Mcp\Tools;

use App\Mcp\Organization\OrganizationReads;
use App\Mcp\Organization\OwnerContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListOrganizationRecords extends Tool
{
    protected string $description = 'List organization properties, units, residents, technicians, maintenance requests, or activity logs with bounded pagination and optional search.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()->enum(['properties', 'units', 'residents', 'technicians', 'maintenance-requests', 'activity-logs'])->required(),
            'search' => $schema->string(),
            'status' => $schema->string()->description('Maintenance request status filter only.'),
            'page' => $schema->integer(),
            'per_page' => $schema->integer(),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        return OwnerContext::run(fn (): ResponseFactory => Response::structured(OrganizationReads::listing($request->all())));
    }
}
