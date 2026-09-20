<?php

namespace App\Actions\Organization\Unit;

use App\Actions\Billing\GetUnitCreationAllowance;
use App\Actions\Common\LogActivity;
use App\Data\ActivityLogData;
use App\Data\UnitData;
use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionUnitUsage;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateUnit
{
    public static function handle(
        UnitData $data,
        User $actor,
        Organization $organization,
    ): Unit {
        $allowance = GetUnitCreationAllowance::handle($organization);

        $unit = DB::transaction(function () use ($data, $organization, $allowance): Unit {
            Subscription::query()->whereKey($allowance['subscription_id'])->lockForUpdate()->firstOrFail();

            $usage = SubscriptionUnitUsage::query()->firstOrCreate(
                [
                    'subscription_id' => $allowance['subscription_id'],
                    'period_starts_at' => $allowance['period_starts_at'],
                ],
                [
                    'organization_id' => $organization->id,
                    'plan_id' => $allowance['plan_id'],
                    'period_ends_at' => $allowance['period_ends_at'],
                    'units_created' => 0,
                ],
            );

            if ($usage->units_created >= $allowance['limit']) {
                throw ValidationException::withMessages([
                    'cannot_submit' => "This organization has used all {$allowance['limit']} unit creations for the current billing period.",
                ]);
            }

            $unit = Unit::create($data->toArray());
            $usage->increment('units_created');

            return $unit;
        });

        self::logActivity($unit, $actor, $organization);

        return $unit;
    }

    private static function logActivity(
        Unit $unit,
        User $actor,
        Organization $organization,
    ): void {
        LogActivity::handle(ActivityLogData::from([
            'event' => ActivityEventEnum::UNIT_CREATED,
            'title' => 'Unit created',
            'description' => Str::swap([
                ':actor' => $actor->name,
                ':unit' => $unit->name,
            ], ':actor created unit ":unit".'),
            'subject' => $unit,
            'actor' => $actor,
            'organization' => $organization,
        ]));
    }
}
