<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareAssignTechnician extends PrepareOrganizationChange
{
    protected string $operation = 'assign-technician';

    protected string $description = 'Preview assigning an available technician to a maintenance request; confirmation notifies the technician.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Maintenance request ID')->required(),
            'assigned_technician_id' => $schema->integer()->required(),
            'notes' => $schema->string()->nullable(),
        ];
    }
}
