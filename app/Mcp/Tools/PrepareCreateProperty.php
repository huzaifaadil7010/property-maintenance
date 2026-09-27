<?php

namespace App\Mcp\Tools;

use App\Enums\PropertyType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareCreateProperty extends PrepareOrganizationChange
{
    protected string $operation = 'create-property';

    protected string $description = 'Preview creating a property. Returns a token for confirm-organization-change; does not create the property.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'type' => $schema->string()->enum(array_column(PropertyType::cases(), 'value'))->required(),
            'address' => $schema->string()->required(),
            'city' => $schema->string()->required(),
        ];
    }
}
