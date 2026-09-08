<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int|null $current_organization_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['current_organization_id', 'name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements HasMedia, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, InteractsWithMedia, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->using(OrganizationUser::class)->withPivot(['id', 'is_active'])->withTimestamps();
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class, 'resident_id');
    }

    public function technicianProfiles(): HasMany
    {
        return $this->hasMany(TechnicianProfile::class);
    }

    public function reportedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'resident_id');
    }

    public function assignedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_technician_id');
    }

    public function maintenanceRequestStatusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class, 'changed_by');
    }

    public function getDashboardUrl(): string
    {
        if ($this->hasRole(UserRole::RESIDENT)) {
            return route('resident.dashboard');
        }

        if ($this->hasRole(UserRole::TECHNICIAN)) {
            return route('technician.dashboard');
        }

        if (
            (
                $this->hasRole(UserRole::OWNER)
                || $this->hasRole(UserRole::MANAGER)
            ) && $this->currentOrganization?->uuid !== null
        ) {
            return route('organization.dashboard', [
                'organization' => $this->currentOrganization->uuid,
            ]);
        }

        return route('home');
    }

    #[Scope]
    protected function owner(Builder $builder): Builder
    {
        return $builder->role(UserRole::OWNER);
    }

    #[Scope]
    protected function manager(Builder $builder): Builder
    {
        return $builder->role(UserRole::MANAGER);
    }

    #[Scope]
    protected function resident(Builder $builder): Builder
    {
        return $builder->role(UserRole::RESIDENT);
    }

    #[Scope]
    protected function technician(Builder $builder): Builder
    {
        return $builder->role(UserRole::TECHNICIAN);
    }
}
