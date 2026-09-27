<?php

namespace App\Mcp\Tools;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\OrganizationChangeOperationEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareUpdateMaintenanceStatus extends PrepareOrganizationChange
{
    protected OrganizationChangeOperationEnum $operation = OrganizationChangeOperationEnum::UPDATE_MAINTENANCE_STATUS;

    protected string $description = 'Preview changing a maintenance request status. A technician must already be assigned.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Maintenance request ID')->required(),
            'status' => $schema->string()->enum(array_column(MaintenanceRequestStatus::cases(), 'value'))->required(),
            'notes' => $schema->string()->nullable(),
        ];
    }
}
