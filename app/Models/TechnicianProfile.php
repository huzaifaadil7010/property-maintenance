<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\TechnicianSpecialty;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'user_id', 'specialty', 'phone', 'is_available'])]
class TechnicianProfile extends Model
{
    use BelongsToOrganization;

    protected $attributes = ['is_available' => true];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['specialty' => TechnicianSpecialty::class, 'is_available' => 'boolean'];
    }
}
