<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

#[Fillable(['uuid', 'name', 'slug', 'email', 'phone'])]
class Organization extends Model
{
    use Billable;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(OrganizationUser::class)->withPivot(['id', 'is_active'])->withTimestamps();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class);
    }

    public function technicianProfiles(): HasMany
    {
        return $this->hasMany(TechnicianProfile::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function maintenanceRequestStatusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function hasValidSubscription(): bool
    {
        return $this->subscription('default')?->valid() ?? false;
    }

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime'];
    }
}
