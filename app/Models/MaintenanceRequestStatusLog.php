<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\MaintenanceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'maintenance_request_id', 'changed_by', 'from_status', 'to_status', 'notes'])]
class MaintenanceRequestStatusLog extends Model
{
    use BelongsToOrganization;

    public const UPDATED_AT = null;

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected function casts(): array
    {
        return ['from_status' => MaintenanceRequestStatus::class, 'to_status' => MaintenanceRequestStatus::class];
    }
}
