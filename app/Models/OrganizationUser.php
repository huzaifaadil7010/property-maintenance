<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table(incrementing: true)]
#[Fillable(['organization_id', 'user_id', 'is_active'])]
class OrganizationUser extends Pivot
{
    use BelongsToOrganization;

    protected $attributes = ['is_active' => true];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
