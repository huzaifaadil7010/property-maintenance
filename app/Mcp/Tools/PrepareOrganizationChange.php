<?php

namespace App\Mcp\Tools;

use App\Mcp\Organization\OrganizationChange;
use App\Mcp\Organization\OwnerContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

abstract class PrepareOrganizationChange extends Tool
{
    protected string $operation;

    public function handle(Request $request): ResponseFactory
    {
        return OwnerContext::run(
            fn ($owner, $organization): ResponseFactory => Response::structured(
                OrganizationChange::prepare($this->operation, $request->all(), $owner, $organization),
            ),
        );
    }
}
