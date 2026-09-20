<?php

namespace App\Actions\Billing;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class GetActivePlans
{
    public static function handle(): Collection
    {
        return Plan::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
