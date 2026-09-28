<?php

namespace App\Mcp\Tools;

use App\Enums\OrganizationChangeOperationEnum;
use App\Enums\TechnicianSpecialty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareCreateTechnician extends PrepareOrganizationChange
{
    protected OrganizationChangeOperationEnum $operation = OrganizationChangeOperationEnum::CREATE_TECHNICIAN;

    protected string $description = 'Preview creating a technician. Confirmation emails account credentials to the technician.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'email' => $schema->string()->required(),
            'phone' => $schema->string()->nullable(),
            'specialty' => $schema->string()->enum(array_column(TechnicianSpecialty::cases(), 'value'))->required(),
            'is_available' => $schema->boolean(),
        ];
    }
}
