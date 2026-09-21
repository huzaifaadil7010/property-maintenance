<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'operations'],
            [
                'name' => 'Operations Tier',
                'description' => 'Full feature access for small and mid-sized residential portfolios.',
                'stripe_price_id' => config('billing.operations_price_id'),
                'amount' => 1000,
                'unit_creation_limit' => 250,
                'currency' => 'usd',
                'billing_interval' => 'month',
                'billing_interval_count' => 1,
                'trial_days' => 14,
                'features' => [
                    'resident_and_technician_seats' => 'Unlimited',
                    'automated_sla_alerts_and_audit_history' => true,
                    'photo_and_video_intake_storage' => true,
                ],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
        );
    }
}
