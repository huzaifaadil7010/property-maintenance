<?php

namespace App\Mcp\Tools;

use App\Enums\OrganizationChangeOperationEnum;
use App\Enums\PropertyType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareUpdateProperty extends PrepareOrganizationChange
{
    protected OrganizationChangeOperationEnum $operation = OrganizationChangeOperationEnum::UPDATE_PROPERTY;

    protected string $description = 'Preview updating a property by ID. All property fields are required; confirm-organization-change applies it.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'name' => $schema->string()->required(),
            'type' => $schema->string()->enum(array_column(PropertyType::cases(), 'value'))->required(),
            'address' => $schema->string()->required(),
            'city' => $schema->string()->required(),
        ];
    }
}
