<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareDeleteUnit extends PrepareOrganizationChange
{
    protected string $operation = 'delete-unit';

    protected string $description = 'Preview deleting a unit and cascading occupancy and maintenance records.';

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->required()];
    }
}
