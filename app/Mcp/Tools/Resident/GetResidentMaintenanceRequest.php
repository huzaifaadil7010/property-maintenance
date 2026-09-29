<?php

namespace App\Mcp\Tools\Resident;

use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentReads;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetResidentMaintenanceRequest extends Tool
{
    protected string $description = 'Get one of the configured resident\'s own maintenance requests with description, status history, and image attachment IDs.';

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->required()];
    }

    public function handle(Request $request): ResponseFactory
    {
        $input = $request->validate(['id' => ['required', 'integer', 'min:1']]);

        return ResidentContext::run(fn ($resident): ResponseFactory => Response::structured(ResidentReads::detail($input['id'], $resident)));
    }
}
