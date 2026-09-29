<?php

namespace App\Mcp\Tools\Resident;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\ResidentChangeOperationEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PrepareCreateResidentMaintenanceRequest extends PrepareResidentChange
{
    protected ResidentChangeOperationEnum $operation = ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST;

    protected string $description = 'Preview reporting an issue from this resident\'s active residence. Requires 1–10 image filenames already placed in storage/app/private/mcp/resident-inbox; does not create the request or upload images. Confirm with confirm-resident-change after user approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'category' => $schema->string()->enum(array_column(MaintenanceCategory::cases(), 'value'))->required(),
            'priority' => $schema->string()->enum(array_column(MaintenancePriority::cases(), 'value'))->required(),
            'description' => $schema->string()->required(),
            'images' => $schema->array()->items($schema->string())->description('Filenames only, not paths; 1–10 JPEG, PNG, or WebP images in the resident inbox.')->required(),
        ];
    }
}
