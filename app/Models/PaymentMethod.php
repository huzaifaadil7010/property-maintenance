<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'stripe_payment_method_id', 'type', 'brand', 'last_four', 'exp_month', 'exp_year', 'is_default'])]
class PaymentMethod extends Model
{
    use BelongsToOrganization;

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected function casts(): array
    {
        return [
            'exp_month' => 'integer',
            'exp_year' => 'integer',
            'is_default' => 'boolean',
        ];
    }
}
