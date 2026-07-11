<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'property_id', 'unit_id', 'resident_id', 'assigned_technician_id', 'title', 'description', 'category', 'priority', 'status', 'completion_notes', 'actual_cost', 'completed_at', 'closed_at'])]
class MaintenanceRequest extends Model
{
    use BelongsToOrganization;

    protected $attributes = [
        'priority' => MaintenancePriority::NORMAL->value,
        'status' => MaintenanceRequestStatus::OPEN->value,
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceRequestAttachment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class);
    }

    protected function casts(): array
    {
        return [
            'category' => MaintenanceCategory::class,
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceRequestStatus::class,
            'actual_cost' => 'decimal:2',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
