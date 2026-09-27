<?php

namespace App\Mcp\Tools;

use App\Enums\UnitStatus;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareUpdateUnit extends PrepareOrganizationChange
{
    protected string $operation = 'update-unit';

    protected string $description = 'Preview updating a unit by ID. All unit fields except floor are required.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'property_id' => $schema->integer()->required(),
            'name' => $schema->string()->required(),
            'floor' => $schema->string()->nullable(),
            'status' => $schema->string()->enum(array_column(UnitStatus::cases(), 'value'))->required(),
        ];
    }
}
