<?php

namespace App\Mcp\Tools;

use App\Mcp\Organization\OrganizationReads;
use App\Mcp\Organization\OwnerContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetOrganizationOptions extends Tool
{
    protected string $description = 'Get valid property, available unit, available technician, and enum options for organization changes.';

    public function handle(Request $request): ResponseFactory
    {
        return OwnerContext::run(fn (): ResponseFactory => Response::structured(OrganizationReads::options()));
    }
}
