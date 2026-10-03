<?php

namespace App\Mcp\Tools;

use App\Enums\OrganizationChangeOperationEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareUpdateResident extends PrepareOrganizationChange
{
    protected OrganizationChangeOperationEnum $operation = OrganizationChangeOperationEnum::UPDATE_RESIDENT;

    protected string $description = 'Preview updating an existing resident and assigning or changing their property and unit. All fields except phone are required.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'name' => $schema->string()->required(),
            'email' => $schema->string()->required(),
            'phone' => $schema->string()->nullable(),
            'property_id' => $schema->integer()->required(),
            'unit_id' => $schema->integer()->required(),
        ];
    }
}
