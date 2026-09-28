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
class GetResidentDashboardData extends Tool
{
    protected string $description = 'Get the configured resident\'s current residence, request totals, and five most recent maintenance requests.';

    public function handle(Request $request): ResponseFactory
    {
        return ResidentContext::run(fn ($resident): ResponseFactory => Response::structured(ResidentReads::dashboard($resident)));
    }
}
