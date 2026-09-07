<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['organization_id', 'property_id', 'unit_id', 'resident_id', 'assigned_technician_id', 'title', 'description', 'category', 'priority', 'status', 'completion_notes', 'actual_cost', 'completed_at', 'closed_at'])]
class MaintenanceRequest extends Model implements HasMedia
{
    use BelongsToOrganization, InteractsWithMedia;

    public const MEDIA_COLLECTION_ISSUE_IMAGES = 'issue-images';

    public const MEDIA_COLLECTION_COMPLETION_IMAGES = 'completion-images';

    protected $attributes = [
        'priority' => MaintenancePriority::NORMAL->value,
        'status' => MaintenanceRequestStatus::OPEN->value,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_ISSUE_IMAGES)
            ->withResponsiveImages();

        $this->addMediaCollection(self::MEDIA_COLLECTION_COMPLETION_IMAGES)
            ->withResponsiveImages();
    }

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

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class);
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::OPEN);
    }

    #[Scope]
    protected function assigned(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::ASSIGNED);
    }

    #[Scope]
    protected function inProgress(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::IN_PROGRESS);
    }

    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::COMPLETED);
    }

    #[Scope]
    protected function closed(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::CLOSED);
    }

    #[Scope]
    protected function reopened(Builder $query): void
    {
        $query->where('status', MaintenanceRequestStatus::REOPENED);
    }

    public function isOpen(): bool
    {
        return $this->status === MaintenanceRequestStatus::OPEN;
    }

    public function isAssigned(): bool
    {
        return $this->status === MaintenanceRequestStatus::ASSIGNED;
    }

    public function isInProgress(): bool
    {
        return $this->status === MaintenanceRequestStatus::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === MaintenanceRequestStatus::COMPLETED;
    }

    public function isClosed(): bool
    {
        return $this->status === MaintenanceRequestStatus::CLOSED;
    }

    public function isReopened(): bool
    {
        return $this->status === MaintenanceRequestStatus::REOPENED;
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
