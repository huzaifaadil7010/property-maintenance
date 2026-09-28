<?php

namespace App\Mcp\Tools\Resident;

use App\Enums\ResidentChangeOperationEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareReopenResidentMaintenanceRequest extends PrepareResidentChange
{
    protected ResidentChangeOperationEnum $operation = ResidentChangeOperationEnum::REOPEN_MAINTENANCE_REQUEST;

    protected string $description = 'Preview reopening one of this resident\'s completed maintenance requests with a required reason. Does not change the request until confirm-resident-change is approved.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Maintenance request ID')->required(),
            'notes' => $schema->string()->description('Reason for reopening, up to 1000 characters.')->required(),
        ];
    }
}
