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

    protected string $description = 'Preview reporting an issue from this resident\'s active residence with 1–10 image IDs uploaded through the resident issue-image picker. Does not create the request. Confirm only after user approval.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'category' => $schema->string()->enum(array_column(MaintenanceCategory::cases(), 'value'))->required(),
            'priority' => $schema->string()->enum(array_column(MaintenancePriority::cases(), 'value'))->required(),
            'description' => $schema->string()->required(),
            'images' => $schema->array()->items($schema->string())->description('1–10 opaque image IDs returned by the resident issue-image upload tool; never filenames or paths.')->required(),
        ];
    }
}
