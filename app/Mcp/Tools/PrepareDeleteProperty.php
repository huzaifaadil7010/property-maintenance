<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareDeleteProperty extends PrepareOrganizationChange
{
    protected string $operation = 'delete-property';

    protected string $description = 'Preview deleting a property and cascading related records. Review impact before confirming.';

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->required()];
    }
}
