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
class GetOrganizationRecord extends Tool
{
    protected string $description = 'Get one property, unit, resident, technician, or maintenance request in the current organization by ID.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'resource' => $schema->string()->enum(['properties', 'units', 'residents', 'technicians', 'maintenance-requests'])->required(),
            'id' => $schema->integer()->required(),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        return OwnerContext::run(fn ($owner, $organization): ResponseFactory => Response::structured(OrganizationReads::detail($request->all(), $organization)));
    }
}
