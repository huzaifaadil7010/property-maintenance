<?php

namespace Database\Seeders;

use App\Enums\AttachmentType;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\OccupancyStatus;
use App\Enums\TechnicianSpecialty;
use App\Enums\UserRole;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class MaintenanceRequestSeeder extends Seeder
{
    private const int REQUESTS_PER_ORGANIZATION = 5;

    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        DB::transaction(function (): void {
            $timestamp = now();
            $portfolios = $this->portfolios();
            $scenarios = $this->scenarios();

            if (count($scenarios) !== 10) {
                throw new RuntimeException('The maintenance request seeder must contain exactly 10 scenarios.');
            }

            $organizations = DB::table('organizations')
                ->whereIn('slug', array_keys($portfolios))
                ->get(['id', 'slug'])
                ->keyBy('slug');

            if ($organizations->count() !== count($portfolios)) {
                throw new RuntimeException('Run OwnerPortfolioSeeder before MaintenanceRequestSeeder.');
            }

            $ownerIds = DB::table('users')
                ->whereIn('email', array_column($portfolios, 'owner_email'))
                ->pluck('id', 'email');

            if ($ownerIds->count() !== count($portfolios)) {
                throw new RuntimeException('The seeded portfolio owners are required for maintenance history.');
            }

            $technicianIds = $this->seedTechnicians($organizations, $timestamp);
            $requestRows = [];
            $requestContexts = [];
            $scenarioIndex = 0;

            foreach ($portfolios as $organizationSlug => $portfolio) {
                $organization = $organizations->get($organizationSlug);
                $occupancies = DB::table('occupancies')
                    ->join('units', 'units.id', '=', 'occupancies.unit_id')
                    ->where('occupancies.organization_id', $organization->id)
                    ->where('occupancies.status', OccupancyStatus::ACTIVE->value)
                    ->oldest('occupancies.id')
                    ->limit(self::REQUESTS_PER_ORGANIZATION)
                    ->get([
                        'occupancies.resident_id',
                        'occupancies.unit_id',
                        'units.property_id',
                    ]);

                if ($occupancies->count() !== self::REQUESTS_PER_ORGANIZATION) {
                    throw new RuntimeException("Organization {$organizationSlug} requires five active occupancies.");
                }

                foreach ($occupancies as $occupancy) {
                    $scenario = $scenarios[$scenarioIndex];
                    $createdAt = $timestamp->copy()
                        ->subDays(10 - $scenarioIndex)
                        ->startOfDay()
                        ->addHours(9 + ($scenarioIndex % 4));
                    $assignedTechnicianId = $scenario['technician'] === null
                        ? null
                        : $technicianIds[$organization->id][$scenario['technician']];

                    $requestRows[] = [
                        'organization_id' => $organization->id,
                        'property_id' => $occupancy->property_id,
                        'unit_id' => $occupancy->unit_id,
                        'resident_id' => $occupancy->resident_id,
                        'assigned_technician_id' => $assignedTechnicianId,
                        'title' => $scenario['title'],
                        'description' => $scenario['description'],
                        'category' => $scenario['category']->value,
                        'priority' => $scenario['priority']->value,
                        'status' => $scenario['status']->value,
                        'completion_notes' => $scenario['completion_notes'],
                        'actual_cost' => $scenario['actual_cost'],
                        'completed_at' => $scenario['completed_after_hours'] === null
                            ? null
                            : $createdAt->copy()->addHours($scenario['completed_after_hours']),
                        'closed_at' => $scenario['closed_after_hours'] === null
                            ? null
                            : $createdAt->copy()->addHours($scenario['closed_after_hours']),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt->copy()->addHours(count($scenario['transitions']) * 4),
                    ];

                    $requestContexts[] = [
                        'organization_id' => $organization->id,
                        'owner_id' => $ownerIds->get($portfolio['owner_email']),
                        'resident_id' => $occupancy->resident_id,
                        'technician_id' => $assignedTechnicianId,
                        'created_at' => $createdAt,
                        'scenario' => $scenario,
                    ];

                    $scenarioIndex++;
                }
            }

            $organizationIds = $organizations->pluck('id');
            $titles = array_column($requestRows, 'title');

            DB::table('maintenance_requests')
                ->whereIn('organization_id', $organizationIds)
                ->whereIn('title', $titles)
                ->delete();

            DB::table('maintenance_requests')->insert($requestRows);

            $requestIds = DB::table('maintenance_requests')
                ->whereIn('organization_id', $organizationIds)
                ->whereIn('title', $titles)
                ->get(['id', 'organization_id', 'title'])
                ->keyBy(fn (object $request): string => $this->relationshipKey($request->organization_id, $request->title))
                ->map(fn (object $request): int => $request->id);

            $statusLogRows = [];
            $attachmentRows = [];

            foreach ($requestContexts as $requestContext) {
                $scenario = $requestContext['scenario'];
                $requestId = $requestIds->get($this->relationshipKey(
                    $requestContext['organization_id'],
                    $scenario['title'],
                ));
                $fromStatus = null;

                foreach ($scenario['transitions'] as $transitionIndex => $transition) {
                    $statusLogRows[] = [
                        'organization_id' => $requestContext['organization_id'],
                        'maintenance_request_id' => $requestId,
                        'changed_by' => $this->statusActorId($transition['actor'], $requestContext),
                        'from_status' => $fromStatus?->value,
                        'to_status' => $transition['status']->value,
                        'notes' => $transition['notes'],
                        'created_at' => $requestContext['created_at']->copy()->addHours($transitionIndex * 4),
                    ];

                    $fromStatus = $transition['status'];
                }

                foreach ($scenario['attachments'] as $attachmentIndex => $attachmentType) {
                    $uploadedBy = $attachmentType === AttachmentType::COMPLETION
                        ? $requestContext['technician_id']
                        : $requestContext['resident_id'];
                    $extension = $attachmentType === AttachmentType::COMPLETION ? 'png' : 'jpg';
                    $originalName = sprintf(
                        '%s-%02d.%s',
                        $attachmentType->value,
                        $requestId,
                        $extension,
                    );

                    $attachmentRows[] = [
                        'organization_id' => $requestContext['organization_id'],
                        'maintenance_request_id' => $requestId,
                        'uploaded_by' => $uploadedBy,
                        'type' => $attachmentType->value,
                        'file_path' => "maintenance/seeded/{$requestId}/{$originalName}",
                        'original_name' => $originalName,
                        'mime_type' => $extension === 'png' ? 'image/png' : 'image/jpeg',
                        'size' => 180000 + ($requestId * 1000) + ($attachmentIndex * 500),
                        'created_at' => $requestContext['created_at']->copy()->addMinutes(15 + $attachmentIndex),
                        'updated_at' => $requestContext['created_at']->copy()->addMinutes(15 + $attachmentIndex),
                    ];
                }
            }

            DB::table('maintenance_request_status_logs')->insert($statusLogRows);
            DB::table('maintenance_request_attachments')->insert($attachmentRows);
        });

        $permissionRegistrar->forgetCachedPermissions();
    }

    private function seedTechnicians(Collection $organizations, DateTimeInterface $timestamp): array
    {
        $specialties = [
            'plumbing' => TechnicianSpecialty::PLUMBING,
            'electrical' => TechnicianSpecialty::ELECTRICAL,
            'general' => TechnicianSpecialty::GENERAL_MAINTENANCE,
        ];
        $password = Hash::make('password');
        $technicianRows = [];

        foreach ($organizations as $organization) {
            foreach ($specialties as $key => $specialty) {
                $technicianRows[] = [
                    'current_organization_id' => $organization->id,
                    'name' => sprintf('%s %s Technician', str($organization->slug)->headline(), str($key)->headline()),
                    'email' => "technician.{$key}.{$organization->slug}@example.com",
                    'email_verified_at' => $timestamp,
                    'password' => $password,
                    'phone' => '+92 300 '.str_pad((string) ($organization->id * 1000 + count($technicianRows) + 1), 7, '0', STR_PAD_LEFT),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::table('users')->upsert(
            $technicianRows,
            ['email'],
            ['current_organization_id', 'name', 'email_verified_at', 'password', 'phone', 'updated_at'],
        );

        $technicianUserIds = DB::table('users')
            ->whereIn('email', array_column($technicianRows, 'email'))
            ->pluck('id', 'email');
        $membershipRows = [];
        $profileRows = [];
        $roleRows = [];
        $technicianIds = [];
        $roleId = DB::table(config('permission.table_names.roles'))
            ->whereNull(config('permission.column_names.team_foreign_key'))
            ->where('name', UserRole::TECHNICIAN->value)
            ->where('guard_name', 'web')
            ->value('id');

        if ($roleId === null) {
            throw new RuntimeException('Run RoleSeeder before MaintenanceRequestSeeder.');
        }

        foreach ($organizations as $organization) {
            foreach ($specialties as $key => $specialty) {
                $email = "technician.{$key}.{$organization->slug}@example.com";
                $technicianId = $technicianUserIds->get($email);
                $technicianIds[$organization->id][$key] = $technicianId;
                $membershipRows[] = [
                    'organization_id' => $organization->id,
                    'user_id' => $technicianId,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                $profileRows[] = [
                    'organization_id' => $organization->id,
                    'user_id' => $technicianId,
                    'specialty' => $specialty->value,
                    'phone' => $technicianRows[count($profileRows)]['phone'],
                    'is_available' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                $roleRows[] = [
                    'role_id' => $roleId,
                    'model_type' => User::class,
                    'model_id' => $technicianId,
                    config('permission.column_names.team_foreign_key') => $organization->id,
                ];
            }
        }

        DB::table('organization_user')->upsert(
            $membershipRows,
            ['organization_id', 'user_id'],
            ['is_active', 'updated_at'],
        );
        DB::table('technician_profiles')->upsert(
            $profileRows,
            ['organization_id', 'user_id'],
            ['specialty', 'phone', 'is_available', 'updated_at'],
        );
        DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore($roleRows);

        return $technicianIds;
    }

    private function statusActorId(string $actor, array $requestContext): int
    {
        return match ($actor) {
            'owner' => $requestContext['owner_id'],
            'technician' => $requestContext['technician_id'],
            default => $requestContext['resident_id'],
        };
    }

    private function relationshipKey(int $organizationId, string $title): string
    {
        return $organizationId.'|'.$title;
    }

    private function portfolios(): array
    {
        return [
            'horizon-property-management' => ['owner_email' => 'alex.owner@example.com'],
            'summit-property-care' => ['owner_email' => 'sara.owner@example.com'],
        ];
    }

    private function scenarios(): array
    {
        return [
            [
                'title' => 'Burst pipe beneath kitchen sink',
                'description' => 'Water is leaking continuously from the supply line beneath the kitchen sink and collecting inside the cabinet.',
                'category' => MaintenanceCategory::PLUMBING,
                'priority' => MaintenancePriority::URGENT,
                'status' => MaintenanceRequestStatus::OPEN,
                'technician' => null,
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Resident reported an active water leak.'],
                ],
            ],
            [
                'title' => 'Bedroom sockets have no power',
                'description' => 'All electrical sockets in the main bedroom stopped working while the lights and other rooms remain operational.',
                'category' => MaintenanceCategory::ELECTRICAL,
                'priority' => MaintenancePriority::HIGH,
                'status' => MaintenanceRequestStatus::ASSIGNED,
                'technician' => 'electrical',
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Resident reported the failed socket circuit.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned to the electrical technician for inspection.'],
                ],
            ],
            [
                'title' => 'Air conditioner is blowing warm air',
                'description' => 'The living room air conditioner runs normally but no longer cools the room even at the lowest temperature setting.',
                'category' => MaintenanceCategory::AIR_CONDITIONING,
                'priority' => MaintenancePriority::NORMAL,
                'status' => MaintenanceRequestStatus::IN_PROGRESS,
                'technician' => 'general',
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Cooling problem reported by resident.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned for air-conditioning diagnostics.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Technician started checking refrigerant pressure and filters.'],
                ],
            ],
            [
                'title' => 'Loose kitchen cabinet door',
                'description' => 'The upper kitchen cabinet door is hanging from one hinge and cannot be closed safely.',
                'category' => MaintenanceCategory::CARPENTRY,
                'priority' => MaintenancePriority::LOW,
                'status' => MaintenanceRequestStatus::COMPLETED,
                'technician' => 'general',
                'completion_notes' => 'Replaced both hinges, aligned the cabinet door, and tested the closure.',
                'actual_cost' => '1850.00',
                'completed_after_hours' => 12,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE, AttachmentType::COMPLETION],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Resident reported a loose cabinet door.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned to general maintenance.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Replacement hinges obtained and repair started.'],
                    ['status' => MaintenanceRequestStatus::COMPLETED, 'actor' => 'technician', 'notes' => 'Cabinet door repaired and completion photo uploaded.'],
                ],
            ],
            [
                'title' => 'Bathroom exhaust fan rattling loudly',
                'description' => 'The bathroom exhaust fan makes a loud rattling sound and provides weak ventilation.',
                'category' => MaintenanceCategory::GENERAL,
                'priority' => MaintenancePriority::NORMAL,
                'status' => MaintenanceRequestStatus::CLOSED,
                'technician' => 'general',
                'completion_notes' => 'Cleaned the fan housing and replaced the worn motor bearing.',
                'actual_cost' => '3200.00',
                'completed_after_hours' => 12,
                'closed_after_hours' => 16,
                'attachments' => [AttachmentType::COMPLETION],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Ventilation issue reported.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned to general maintenance.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Fan removed for cleaning and inspection.'],
                    ['status' => MaintenanceRequestStatus::COMPLETED, 'actor' => 'technician', 'notes' => 'Fan repaired and tested successfully.'],
                    ['status' => MaintenanceRequestStatus::CLOSED, 'actor' => 'resident', 'notes' => 'Resident confirmed normal operation.'],
                ],
            ],
            [
                'title' => 'Recurring leak around toilet base',
                'description' => 'Water has appeared around the toilet base again after the previous repair was marked complete.',
                'category' => MaintenanceCategory::PLUMBING,
                'priority' => MaintenancePriority::URGENT,
                'status' => MaintenanceRequestStatus::REOPENED,
                'technician' => 'plumbing',
                'completion_notes' => 'The original seal was replaced, but the leak was reported again.',
                'actual_cost' => '2400.00',
                'completed_after_hours' => 12,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE, AttachmentType::COMPLETION],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Initial leak reported.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned to the plumbing technician.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Toilet removed and seal inspected.'],
                    ['status' => MaintenanceRequestStatus::COMPLETED, 'actor' => 'technician', 'notes' => 'Seal replaced and area tested dry.'],
                    ['status' => MaintenanceRequestStatus::REOPENED, 'actor' => 'resident', 'notes' => 'Resident reported that water returned the following day.'],
                ],
            ],
            [
                'title' => 'Hallway ceiling light flickers',
                'description' => 'The hallway ceiling light flickers intermittently but does not affect other electrical fixtures.',
                'category' => MaintenanceCategory::ELECTRICAL,
                'priority' => MaintenancePriority::LOW,
                'status' => MaintenanceRequestStatus::OPEN,
                'technician' => null,
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Intermittent lighting issue reported.'],
                ],
            ],
            [
                'title' => 'Split AC indoor unit is dripping',
                'description' => 'Condensation is dripping from the indoor air-conditioning unit onto the living room wall.',
                'category' => MaintenanceCategory::AIR_CONDITIONING,
                'priority' => MaintenancePriority::NORMAL,
                'status' => MaintenanceRequestStatus::ASSIGNED,
                'technician' => 'general',
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Resident uploaded a photo of the water damage.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned for drain-line inspection.'],
                ],
            ],
            [
                'title' => 'Distribution board breaker keeps tripping',
                'description' => 'The kitchen circuit breaker trips repeatedly when normal appliances are in use.',
                'category' => MaintenanceCategory::ELECTRICAL,
                'priority' => MaintenancePriority::HIGH,
                'status' => MaintenanceRequestStatus::IN_PROGRESS,
                'technician' => 'electrical',
                'completion_notes' => null,
                'actual_cost' => null,
                'completed_after_hours' => null,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::ISSUE],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Repeated breaker trips reported.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Urgent electrical inspection assigned.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Circuit load and breaker condition are being tested.'],
                ],
            ],
            [
                'title' => 'Shower drain blockage cleared',
                'description' => 'The shower drained very slowly and overflowed during normal use.',
                'category' => MaintenanceCategory::PLUMBING,
                'priority' => MaintenancePriority::URGENT,
                'status' => MaintenanceRequestStatus::COMPLETED,
                'technician' => 'plumbing',
                'completion_notes' => 'Removed the blockage, flushed the drain line, and confirmed normal flow.',
                'actual_cost' => '1500.00',
                'completed_after_hours' => 12,
                'closed_after_hours' => null,
                'attachments' => [AttachmentType::COMPLETION],
                'transitions' => [
                    ['status' => MaintenanceRequestStatus::OPEN, 'actor' => 'resident', 'notes' => 'Blocked shower drain reported.'],
                    ['status' => MaintenanceRequestStatus::ASSIGNED, 'actor' => 'owner', 'notes' => 'Assigned to the plumbing technician.'],
                    ['status' => MaintenanceRequestStatus::IN_PROGRESS, 'actor' => 'technician', 'notes' => 'Drain clearing work started.'],
                    ['status' => MaintenanceRequestStatus::COMPLETED, 'actor' => 'technician', 'notes' => 'Drain cleared and tested.'],
                ],
            ],
        ];
    }
}
