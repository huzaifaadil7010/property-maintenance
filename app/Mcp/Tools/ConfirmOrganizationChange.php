<?php

namespace App\Mcp\Tools;

use App\Mcp\Organization\OrganizationChange;
use App\Mcp\Organization\OwnerContext;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class ConfirmOrganizationChange extends Tool
{
    protected string $description = 'Execute one previewed organization change after the user reviews and approves its summary. Tokens expire after five minutes and can only be used once.';

    public function schema(JsonSchema $schema): array
    {
        return ['confirmation_token' => $schema->string()->required()];
    }

    public function handle(Request $request): ResponseFactory
    {
        $input = $request->validate(['confirmation_token' => ['required', 'string', 'size:64']]);

        return OwnerContext::run(
            fn ($owner, $organization): ResponseFactory => Response::structured(
                OrganizationChange::confirm($input['confirmation_token'], $owner, $organization),
            ),
        );
    }
}
