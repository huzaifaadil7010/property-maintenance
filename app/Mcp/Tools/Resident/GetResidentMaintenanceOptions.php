<?php

namespace App\Mcp\Tools\Resident;

use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentReads;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetResidentMaintenanceOptions extends Tool
{
    protected string $description = 'Get valid maintenance request categories and priorities for this resident.';

    public function handle(Request $request): ResponseFactory
    {
        return ResidentContext::run(fn (): ResponseFactory => Response::structured(ResidentReads::options()));
    }
}
