<?php

namespace App\Mcp\Tools\Resident;

use App\Mcp\Resident\ResidentChange;
use App\Mcp\Resident\ResidentContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class ConfirmResidentChange extends Tool
{
    protected string $description = 'Execute one previewed resident change only after the user reviews and approves it. Tokens expire five minutes after preview creation and can be used once. Creating a request moves uploaded issue images to permanent request media.';

    public function schema(JsonSchema $schema): array
    {
        return ['confirmation_token' => $schema->string()->required()];
    }

    public function handle(Request $request): ResponseFactory
    {
        $input = $request->validate(['confirmation_token' => ['required', 'string', 'size:64']]);

        return ResidentContext::run(fn ($resident, $organization): ResponseFactory => Response::structured(
            ResidentChange::confirm($input['confirmation_token'], $resident, $organization),
        ));
    }
}
