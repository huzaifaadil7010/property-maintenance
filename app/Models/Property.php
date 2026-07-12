<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'type', 'address', 'city'])]
class Property extends Model
{
    use BelongsToOrganization;

    protected $attributes = ['type' => PropertyType::APARTMENT->value];

    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
