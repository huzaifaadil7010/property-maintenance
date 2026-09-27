<?php

namespace App\Mcp\Tools;

use App\Enums\UnitStatus;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareCreateUnit extends PrepareOrganizationChange
{
    protected string $operation = 'create-unit';

    protected string $description = 'Preview creating a unit in a property. Confirmation consumes one unit-creation allowance.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'property_id' => $schema->integer()->required(),
            'name' => $schema->string()->required(),
            'floor' => $schema->string()->nullable(),
            'status' => $schema->string()->enum(array_column(UnitStatus::cases(), 'value'))->required(),
        ];
    }
}
