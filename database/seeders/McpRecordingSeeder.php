<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Stripe\Subscription as StripeSubscription;

class McpRecordingSeeder extends Seeder
{
    private const string LOCAL_SUBSCRIPTION_ID = 'sub_local_mcp_recording_northstar';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('McpRecordingSeeder may only run in the local environment.');
        }

        $stripeSecret = config('cashier.secret');

        if (! is_string($stripeSecret) || ! str_starts_with($stripeSecret, 'sk_test_')) {
            throw new RuntimeException('McpRecordingSeeder requires a Stripe test-mode secret key.');
        }

        $allowedEmails = [
            DemoDataset::OWNER_EMAIL,
            DemoDataset::FEATURED_RESIDENT_EMAIL,
            ...array_column(DemoDataset::technicians(), 'email'),
        ];

        $hasOtherOrganizations = DB::table('organizations')
            ->where('slug', '!=', DemoDataset::ORGANIZATION_SLUG)
            ->exists();
        $hasOtherUsers = DB::table('users')->whereNotIn('email', $allowedEmails)->exists();

        if ($hasOtherOrganizations || $hasOtherUsers) {
            throw new RuntimeException('McpRecordingSeeder needs a fresh, separate database; existing organizations or users were left untouched.');
        }

        $this->call([PlanSeeder::class, RoleSeeder::class]);

        $organizationId = DB::transaction(function (): int {
            $organizationId = $this->seedOrganization();
            $roleIds = $this->roleIds();
            $passwordHash = Hash::make(DemoDataset::PASSWORD);

            $owner = DemoDataset::owner();
            $this->seedMember($organizationId, $owner, $passwordHash, $roleIds[UserRole::OWNER->value]);

            $resident = collect(DemoDataset::residents())
                ->firstWhere('email', DemoDataset::FEATURED_RESIDENT_EMAIL);

            if ($resident === null) {
                throw new RuntimeException('The featured MCP resident is missing from DemoDataset.');
            }

            $this->seedMember($organizationId, $resident, $passwordHash, $roleIds[UserRole::RESIDENT->value]);

            foreach (DemoDataset::technicians() as $technician) {
                $technicianId = $this->seedMember(
                    $organizationId,
                    $technician,
                    $passwordHash,
                    $roleIds[UserRole::TECHNICIAN->value],
                );

                DB::table('technician_profiles')->updateOrInsert(
                    ['organization_id' => $organizationId, 'user_id' => $technicianId],
                    [
                        'specialty' => $technician['specialty']->value,
                        'phone' => $technician['phone'],
                        'is_available' => $technician['is_available'],
                        'updated_at' => now(),
                    ],
                );
            }

            return $organizationId;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedStripeSubscription($organizationId);
    }

    private function seedOrganization(): int
    {
        $organization = DemoDataset::organization();

        DB::table('organizations')->updateOrInsert(
            ['slug' => DemoDataset::ORGANIZATION_SLUG],
            [
                ...$organization,
                'updated_at' => now(),
            ],
        );

        return DB::table('organizations')
            ->where('slug', DemoDataset::ORGANIZATION_SLUG)
            ->value('id');
    }

    private function seedMember(int $organizationId, array $member, string $passwordHash, int $roleId): int
    {
        DB::table('users')->updateOrInsert(
            ['email' => $member['email']],
            [
                'current_organization_id' => $organizationId,
                'name' => $member['name'],
                'email_verified_at' => now(),
                'password' => $passwordHash,
                'phone' => $member['phone'],
                'updated_at' => now(),
            ],
        );

        $userId = DB::table('users')->where('email', $member['email'])->value('id');

        DB::table('organization_user')->updateOrInsert(
            ['organization_id' => $organizationId, 'user_id' => $userId],
            ['is_active' => true, 'updated_at' => now()],
        );

        DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $userId,
            config('permission.column_names.team_foreign_key') => $organizationId,
        ]);

        return $userId;
    }

    private function seedStripeSubscription(int $organizationId): void
    {
        $organization = Organization::query()->findOrFail($organizationId);
        $plan = Plan::query()->where('slug', 'operations')->firstOrFail();
        $existingSubscriptions = $organization->subscriptions()
            ->where('type', 'default')
            ->get();
        $existingStripeSubscription = $existingSubscriptions
            ->first(fn ($subscription): bool => $subscription->stripe_id !== self::LOCAL_SUBSCRIPTION_ID);

        if ($existingStripeSubscription !== null) {
            $remoteSubscription = $existingStripeSubscription->asStripeSubscription();
            $remotePriceId = $remoteSubscription->items->data[0]->price->id ?? null;
            $matchesDemoPlan = $remotePriceId === $plan->stripe_price_id;
            $subscriptionIsValid = in_array($remoteSubscription->status, [
                StripeSubscription::STATUS_ACTIVE,
                StripeSubscription::STATUS_TRIALING,
            ], true);

            if (! $matchesDemoPlan || ! $subscriptionIsValid) {
                throw new RuntimeException('The organization already has a different or inactive Stripe subscription; it was left untouched.');
            }

            $existingStripeSubscription->update([
                'plan_id' => $plan->id,
                'stripe_status' => $remoteSubscription->status,
            ]);
        } else {
            $stripeSubscription = $organization
                ->newSubscription('default', $plan->stripe_price_id)
                ->trialDays($plan->trial_days)
                ->create(subscriptionOptions: [
                    'trial_settings' => [
                        'end_behavior' => ['missing_payment_method' => 'cancel'],
                    ],
                    'metadata' => ['source' => 'local_mcp_recording_seeder'],
                ]);

            $stripeSubscription->update(['plan_id' => $plan->id]);
        }

        $existingSubscriptions
            ->where('stripe_id', self::LOCAL_SUBSCRIPTION_ID)
            ->each(fn ($syntheticSubscription) => $syntheticSubscription->delete());
    }

    private function roleIds(): array
    {
        $roleIds = DB::table(config('permission.table_names.roles'))
            ->whereNull(config('permission.column_names.team_foreign_key'))
            ->where('guard_name', 'web')
            ->whereIn('name', [
                UserRole::OWNER->value,
                UserRole::RESIDENT->value,
                UserRole::TECHNICIAN->value,
            ])
            ->pluck('id', 'name');

        if ($roleIds->count() !== 3) {
            throw new RuntimeException('The owner, resident, and technician roles could not be seeded.');
        }

        return $roleIds->all();
    }
}
