<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function unitUsages(): HasMany
    {
        return $this->hasMany(SubscriptionUnitUsage::class);
    }

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
        ];
    }
}
