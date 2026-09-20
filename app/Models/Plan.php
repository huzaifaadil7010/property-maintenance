<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'stripe_price_id', 'unit_amount', 'currency', 'billing_interval', 'billing_interval_count', 'trial_days', 'features', 'is_active', 'is_featured', 'sort_order'])]
class Plan extends Model
{
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function unitLimit(): ?int
    {
        $limit = $this->features['units'] ?? null;

        return is_int($limit) ? $limit : null;
    }

    public function supportsUnitCount(int $unitCount): bool
    {
        return $unitCount > 0 && ($this->unitLimit() === null || $unitCount <= $this->unitLimit());
    }

    protected function casts(): array
    {
        return [
            'unit_amount' => 'integer',
            'billing_interval_count' => 'integer',
            'trial_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
