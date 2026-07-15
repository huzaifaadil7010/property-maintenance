<?php

namespace Database\Seeders;

use App\Enums\OccupancyStatus;
use App\Enums\PropertyType;
use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class OwnerPortfolioSeeder extends Seeder
{
    private const int BATCH_SIZE = 100;

    private const int UNITS_PER_PROPERTY = 30;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function (): void {
            $timestamp = now();
            $password = Hash::make('password');
            $portfolios = $this->ownerPortfolios();

            $organizationRows = [];

            foreach ($portfolios as $portfolio) {
                $organizationRows[] = [
                    ...$portfolio['organization'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            $this->upsertInBatches(
                'organizations',
                $organizationRows,
                ['slug'],
                ['uuid', 'name', 'email', 'phone', 'updated_at'],
            );

            $organizationIds = DB::table('organizations')
                ->whereIn('slug', array_column($organizationRows, 'slug'))
                ->pluck('id', 'slug');

            $ownerRows = [];

            foreach ($portfolios as $portfolio) {
                $ownerRows[] = [
                    'current_organization_id' => $organizationIds->get($portfolio['organization']['slug']),
                    'name' => $portfolio['owner']['name'],
                    'email' => $portfolio['owner']['email'],
                    'email_verified_at' => $timestamp,
                    'password' => $password,
                    'phone' => $portfolio['owner']['phone'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            $this->upsertInBatches(
                'users',
                $ownerRows,
                ['email'],
                ['current_organization_id', 'name', 'email_verified_at', 'password', 'phone', 'updated_at'],
            );

            $ownerIds = DB::table('users')
                ->whereIn('email', array_column($ownerRows, 'email'))
                ->pluck('id', 'email');

            $propertyRows = $this->propertyRows($portfolios, $organizationIds, $timestamp);

            $this->upsertInBatches(
                'properties',
                $propertyRows,
                ['organization_id', 'name'],
                ['type', 'address', 'city', 'updated_at'],
            );

            $propertyIds = DB::table('properties')
                ->whereIn('organization_id', $organizationIds->values())
                ->get(['id', 'organization_id', 'name'])
                ->keyBy(fn (object $property): string => $this->relationshipKey($property->organization_id, $property->name))
                ->map(fn (object $property): int => $property->id);

            [$unitRows, $residentRows] = $this->unitAndResidentRows(
                $portfolios,
                $organizationIds,
                $propertyIds,
                $password,
                $timestamp,
            );

            $this->upsertInBatches(
                'units',
                $unitRows,
                ['property_id', 'name'],
                ['organization_id', 'floor', 'status', 'updated_at'],
            );

            $this->upsertInBatches(
                'users',
                array_map(
                    fn (array $residentRow): array => Arr::only($residentRow, [
                        'current_organization_id',
                        'name',
                        'email',
                        'email_verified_at',
                        'password',
                        'phone',
                        'created_at',
                        'updated_at',
                    ]),
                    $residentRows,
                ),
                ['email'],
                ['current_organization_id', 'name', 'email_verified_at', 'password', 'phone', 'updated_at'],
            );

            $residentIds = DB::table('users')
                ->whereIn('email', array_column($residentRows, 'email'))
                ->pluck('id', 'email');

            $unitIds = DB::table('units')
                ->whereIn('property_id', $propertyIds->values())
                ->get(['id', 'property_id', 'name'])
                ->keyBy(fn (object $unit): string => $this->relationshipKey($unit->property_id, $unit->name))
                ->map(fn (object $unit): int => $unit->id);

            $this->seedMemberships(
                $portfolios,
                $organizationIds,
                $ownerIds,
                $residentRows,
                $residentIds,
                $timestamp,
            );

            $this->seedRoles(
                $portfolios,
                $organizationIds,
                $ownerIds,
                $residentRows,
                $residentIds,
            );

            $this->seedOccupancies(
                $residentRows,
                $residentIds,
                $unitIds,
                $timestamp,
            );
        });

        $permissionRegistrar->forgetCachedPermissions();
    }

    /**
     * @param  array<int, array<string, mixed>>  $portfolios
     * @param  Collection<string, int>  $organizationIds
     * @return array<int, array<string, mixed>>
     */
    private function propertyRows(array $portfolios, Collection $organizationIds, DateTimeInterface $timestamp): array
    {
        $propertyRows = [];

        foreach ($portfolios as $portfolio) {
            $organizationId = $organizationIds->get($portfolio['organization']['slug']);

            for ($propertyIndex = 1; $propertyIndex <= $portfolio['property_count']; $propertyIndex++) {
                $propertyRows[] = [
                    'organization_id' => $organizationId,
                    'name' => $this->propertyName($portfolio, $propertyIndex),
                    'type' => PropertyType::APARTMENT->value,
                    'address' => sprintf('%d %s', 10 + $propertyIndex, $portfolio['street']),
                    'city' => $portfolio['city'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        return $propertyRows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $portfolios
     * @param  Collection<string, int>  $organizationIds
     * @param  Collection<string, int>  $propertyIds
     * @return array{array<int, array<string, mixed>>, array<int, array<string, mixed>>}
     */
    private function unitAndResidentRows(
        array $portfolios,
        Collection $organizationIds,
        Collection $propertyIds,
        string $password,
        DateTimeInterface $timestamp,
    ): array {
        $unitRows = [];
        $residentRows = [];

        foreach ($portfolios as $portfolio) {
            $organizationId = $organizationIds->get($portfolio['organization']['slug']);

            for ($propertyIndex = 1; $propertyIndex <= $portfolio['property_count']; $propertyIndex++) {
                $propertyName = $this->propertyName($portfolio, $propertyIndex);
                $propertyId = $propertyIds->get($this->relationshipKey($organizationId, $propertyName));

                for ($unitIndex = 1; $unitIndex <= self::UNITS_PER_PROPERTY; $unitIndex++) {
                    $unitName = $this->unitName($propertyIndex, $unitIndex);
                    $residentEmail = sprintf(
                        'resident.%s.%02d.%02d@example.com',
                        $portfolio['resident_email_prefix'],
                        $propertyIndex,
                        $unitIndex,
                    );

                    $unitRows[] = [
                        'organization_id' => $organizationId,
                        'property_id' => $propertyId,
                        'name' => $unitName,
                        'floor' => (string) (int) ceil($unitIndex / 6),
                        'status' => UnitStatus::OCCUPIED->value,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];

                    $residentRows[] = [
                        'current_organization_id' => $organizationId,
                        'name' => sprintf('%s Resident %02d-%02d', $portfolio['resident_name_prefix'], $propertyIndex, $unitIndex),
                        'email' => $residentEmail,
                        'email_verified_at' => $timestamp,
                        'password' => $password,
                        'phone' => sprintf('+92 3%02d %07d', $propertyIndex, ($propertyIndex * 1000) + $unitIndex),
                        'organization_id' => $organizationId,
                        'property_id' => $propertyId,
                        'unit_name' => $unitName,
                        'starts_at' => now()->subDays(($propertyIndex * self::UNITS_PER_PROPERTY) + $unitIndex)->toDateString(),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        return [$unitRows, $residentRows];
    }

    /**
     * @param  array<int, array<string, mixed>>  $portfolios
     * @param  Collection<string, int>  $organizationIds
     * @param  Collection<string, int>  $ownerIds
     * @param  array<int, array<string, mixed>>  $residentRows
     * @param  Collection<string, int>  $residentIds
     */
    private function seedMemberships(
        array $portfolios,
        Collection $organizationIds,
        Collection $ownerIds,
        array $residentRows,
        Collection $residentIds,
        DateTimeInterface $timestamp,
    ): void {
        $membershipRows = [];

        foreach ($portfolios as $portfolio) {
            $membershipRows[] = [
                'organization_id' => $organizationIds->get($portfolio['organization']['slug']),
                'user_id' => $ownerIds->get($portfolio['owner']['email']),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach ($residentRows as $residentRow) {
            $membershipRows[] = [
                'organization_id' => $residentRow['organization_id'],
                'user_id' => $residentIds->get($residentRow['email']),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $this->upsertInBatches(
            'organization_user',
            $membershipRows,
            ['organization_id', 'user_id'],
            ['is_active', 'updated_at'],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $portfolios
     * @param  Collection<string, int>  $organizationIds
     * @param  Collection<string, int>  $ownerIds
     * @param  array<int, array<string, mixed>>  $residentRows
     * @param  Collection<string, int>  $residentIds
     */
    private function seedRoles(
        array $portfolios,
        Collection $organizationIds,
        Collection $ownerIds,
        array $residentRows,
        Collection $residentIds,
    ): void {
        $roleIds = DB::table(config('permission.table_names.roles'))
            ->whereNull(config('permission.column_names.team_foreign_key'))
            ->where('guard_name', 'web')
            ->whereIn('name', [UserRole::OWNER->value, UserRole::RESIDENT->value])
            ->pluck('id', 'name');

        if (! $roleIds->has([UserRole::OWNER->value, UserRole::RESIDENT->value])) {
            throw new RuntimeException('Owner and resident roles must be seeded before the owner portfolio.');
        }

        $roleRows = [];

        foreach ($portfolios as $portfolio) {
            $roleRows[] = [
                'role_id' => $roleIds->get(UserRole::OWNER->value),
                'model_type' => User::class,
                'model_id' => $ownerIds->get($portfolio['owner']['email']),
                'organization_id' => $organizationIds->get($portfolio['organization']['slug']),
            ];
        }

        foreach ($residentRows as $residentRow) {
            $roleRows[] = [
                'role_id' => $roleIds->get(UserRole::RESIDENT->value),
                'model_type' => User::class,
                'model_id' => $residentIds->get($residentRow['email']),
                'organization_id' => $residentRow['organization_id'],
            ];
        }

        $this->insertOrIgnoreInBatches(config('permission.table_names.model_has_roles'), $roleRows);
    }

    /**
     * @param  array<int, array<string, mixed>>  $residentRows
     * @param  Collection<string, int>  $residentIds
     * @param  Collection<string, int>  $unitIds
     */
    private function seedOccupancies(
        array $residentRows,
        Collection $residentIds,
        Collection $unitIds,
        DateTimeInterface $timestamp,
    ): void {
        $residentUserIds = $residentIds->values();
        $existingOccupancyIds = DB::table('occupancies')
            ->whereIn('resident_id', $residentUserIds)
            ->get(['id', 'unit_id', 'resident_id'])
            ->keyBy(fn (object $occupancy): string => $this->relationshipKey($occupancy->unit_id, $occupancy->resident_id))
            ->map(fn (object $occupancy): int => $occupancy->id);

        $existingRows = [];
        $newRows = [];

        foreach ($residentRows as $residentRow) {
            $residentId = $residentIds->get($residentRow['email']);
            $unitId = $unitIds->get($this->relationshipKey($residentRow['property_id'], $residentRow['unit_name']));
            $occupancyRow = [
                'organization_id' => $residentRow['organization_id'],
                'unit_id' => $unitId,
                'resident_id' => $residentId,
                'starts_at' => $residentRow['starts_at'],
                'ends_at' => null,
                'status' => OccupancyStatus::ACTIVE->value,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
            $existingOccupancyId = $existingOccupancyIds->get($this->relationshipKey($unitId, $residentId));

            if ($existingOccupancyId === null) {
                $newRows[] = $occupancyRow;

                continue;
            }

            $existingRows[] = ['id' => $existingOccupancyId, ...$occupancyRow];
        }

        $this->upsertInBatches(
            'occupancies',
            $existingRows,
            ['id'],
            ['organization_id', 'unit_id', 'resident_id', 'starts_at', 'ends_at', 'status', 'updated_at'],
        );
        $this->insertInBatches('occupancies', $newRows);
    }

    /**
     * @param  array<string, mixed>  $portfolio
     */
    private function propertyName(array $portfolio, int $propertyIndex): string
    {
        if ($propertyIndex === 1) {
            return $portfolio['first_property_name'];
        }

        return sprintf('%s %02d', $portfolio['property_name_prefix'], $propertyIndex);
    }

    private function unitName(int $propertyIndex, int $unitIndex): string
    {
        $block = chr(65 + (($propertyIndex - 1) % 5));
        $floor = (int) ceil($unitIndex / 6);
        $position = (($unitIndex - 1) % 6) + 1;

        return sprintf('%s-%d%02d', $block, $floor, $position);
    }

    private function relationshipKey(int|string $parentId, int|string $value): string
    {
        return $parentId.'|'.$value;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  non-empty-array<int, non-empty-string>  $uniqueBy
     * @param  array<int, string>  $update
     */
    private function upsertInBatches(string $table, array $rows, array $uniqueBy, array $update): void
    {
        foreach (array_chunk($rows, self::BATCH_SIZE) as $batch) {
            DB::table($table)->upsert($batch, $uniqueBy, $update);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertInBatches(string $table, array $rows): void
    {
        foreach (array_chunk($rows, self::BATCH_SIZE) as $batch) {
            DB::table($table)->insert($batch);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertOrIgnoreInBatches(string $table, array $rows): void
    {
        foreach (array_chunk($rows, self::BATCH_SIZE) as $batch) {
            DB::table($table)->insertOrIgnore($batch);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerPortfolios(): array
    {
        return [
            [
                'owner' => [
                    'name' => 'Alex Morgan',
                    'email' => 'alex.owner@example.com',
                    'phone' => '+92 300 1111111',
                ],
                'organization' => [
                    'uuid' => 'cc16f495-fdb3-4ff8-b9e2-03cbcd8a1bb0',
                    'name' => 'Horizon Property Management',
                    'slug' => 'horizon-property-management',
                    'email' => 'contact@horizon.example.com',
                    'phone' => '+92 300 1111111',
                ],
                'property_count' => 13,
                'first_property_name' => 'Green View Apartments',
                'property_name_prefix' => 'Horizon Residence',
                'resident_name_prefix' => 'Horizon',
                'resident_email_prefix' => 'horizon',
                'street' => 'Garden Road',
                'city' => 'Lahore',
            ],
            [
                'owner' => [
                    'name' => 'Sara Ahmed',
                    'email' => 'sara.owner@example.com',
                    'phone' => '+92 300 2222222',
                ],
                'organization' => [
                    'uuid' => 'a71c804d-3824-4617-81a4-64a417f7cd39',
                    'name' => 'Summit Property Care',
                    'slug' => 'summit-property-care',
                    'email' => 'contact@summit.example.com',
                    'phone' => '+92 300 2222222',
                ],
                'property_count' => 12,
                'first_property_name' => 'City Tower',
                'property_name_prefix' => 'Summit Heights',
                'resident_name_prefix' => 'Summit',
                'resident_email_prefix' => 'summit',
                'street' => 'Main Boulevard',
                'city' => 'Karachi',
            ],
        ];
    }
}
