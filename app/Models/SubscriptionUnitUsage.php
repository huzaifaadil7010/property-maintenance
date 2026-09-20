<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'subscription_id', 'plan_id', 'period_starts_at', 'period_ends_at', 'units_created'])]
class SubscriptionUnitUsage extends Model
{
    use BelongsToOrganization;

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    protected function casts(): array
    {
        return [
            'period_starts_at' => 'datetime',
            'period_ends_at' => 'datetime',
            'units_created' => 'integer',
        ];
    }
}
