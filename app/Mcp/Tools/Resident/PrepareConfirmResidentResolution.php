<?php

namespace App\Mcp\Tools\Resident;

use App\Enums\ResidentChangeOperationEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareConfirmResidentResolution extends PrepareResidentChange
{
    protected ResidentChangeOperationEnum $operation = ResidentChangeOperationEnum::CONFIRM_RESOLUTION;

    protected string $description = 'Preview closing one of this resident\'s completed maintenance requests after confirming the work is resolved. Does not change the request until confirm-resident-change is approved.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Maintenance request ID')->required(),
            'notes' => $schema->string()->nullable(),
        ];
    }
}
