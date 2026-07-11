<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\OccupancyStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'unit_id', 'resident_id', 'starts_at', 'ends_at', 'status'])]
class Occupancy extends Model
{
    use BelongsToOrganization;

    protected $attributes = ['status' => OccupancyStatus::ACTIVE->value];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'ends_at' => 'date', 'status' => OccupancyStatus::class];
    }
}
