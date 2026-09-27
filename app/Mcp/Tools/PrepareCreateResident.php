<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareCreateResident extends PrepareOrganizationChange
{
    protected string $operation = 'create-resident';

    protected string $description = 'Preview creating a resident and active occupancy. Confirmation emails account credentials to the resident.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'email' => $schema->string()->required(),
            'phone' => $schema->string()->nullable(),
            'property_id' => $schema->integer()->required(),
            'unit_id' => $schema->integer()->required(),
        ];
    }
}
