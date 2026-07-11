<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'property_id', 'name', 'floor', 'status'])]
class Unit extends Model
{
    use BelongsToOrganization;

    protected $attributes = ['status' => UnitStatus::VACANT->value];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    protected function casts(): array
    {
        return ['status' => UnitStatus::class];
    }
}
