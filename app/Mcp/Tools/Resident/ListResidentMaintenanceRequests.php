<?php

namespace App\Mcp\Tools\Resident;

use App\Enums\MaintenanceRequestStatus;
use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentReads;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListResidentMaintenanceRequests extends Tool
{
    protected string $description = 'List only the configured resident\'s maintenance requests as compact rows, with search, status, inclusive UTC creation dates, and bounded pagination. Use get-resident-maintenance-request for history and images.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string(),
            'status' => $schema->string()->enum(array_column(MaintenanceRequestStatus::cases(), 'value')),
            'from' => $schema->string()->description('Created on or after this UTC date (YYYY-MM-DD).'),
            'to' => $schema->string()->description('Created on or before this UTC date (YYYY-MM-DD).'),
            'page' => $schema->integer(),
            'per_page' => $schema->integer()->description('One of 10, 20, 25, 30, 40, or 50. Defaults to 10.'),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        return ResidentContext::run(fn ($resident): ResponseFactory => Response::structured(ResidentReads::listing($request->all(), $resident)));
    }
}
