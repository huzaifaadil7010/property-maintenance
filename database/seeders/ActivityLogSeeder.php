<?php

namespace Database\Seeders;

use App\Enums\ActivityEventEnum;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\Support\DemoDataset;
use DateTimeInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = DB::table('organizations')
            ->where('slug', DemoDataset::ORGANIZATION_SLUG)
            ->value('id');
        $owner = DB::table('users')->where('email', DemoDataset::OWNER_EMAIL)->first(['id', 'name']);

        if ($organizationId === null || $owner === null) {
            throw new RuntimeException('Run OwnerPortfolioSeeder before ActivityLogSeeder.');
        }

        DB::table('activity_log')->where('organization_id', $organizationId)->delete();

        $rows = [
            ...$this->portfolioActivityRows($organizationId, $owner),
            ...$this->maintenanceActivityRows($organizationId, $owner),
        ];

        foreach (array_chunk($rows, 100) as $batch) {
            DB::table('activity_log')->insert($batch);
        }
    }

    private function portfolioActivityRows(int $organizationId, object $owner): array
    {
        $rows = [];
        $properties = DB::table('properties')
            ->where('organization_id', $organizationId)
            ->get()
            ->keyBy('name');

        foreach (DemoDataset::properties() as $propertyData) {
            $property = $properties->get($propertyData['name']);
            $rows[] = $this->activityRow(
                ActivityEventEnum::PROPERTY_CREATED,
                'Property created',
                "{$owner->name} created property \"{$property->name}\".",
                $organizationId,
                $owner->id,
                Carbon::parse($property->created_at),
                Property::class,
                $property->id,
            );
        }

        $cedarGrove = $properties->get('Cedar Grove Apartments');
        $rows[] = $this->activityRow(
            ActivityEventEnum::PROPERTY_UPDATED,
            'Property updated',
            "{$owner->name} updated property \"{$cedarGrove->name}\".",
            $organizationId,
            $owner->id,
            Carbon::parse($cedarGrove->created_at)->addDays(3),
            Property::class,
            $cedarGrove->id,
        );
        $rows[] = $this->activityRow(
            ActivityEventEnum::PROPERTY_DELETED,
            'Property deleted',
            "{$owner->name} deleted property \"Harbor Point Annex\".",
            $organizationId,
            $owner->id,
            now()->subDays(61)->startOfDay()->addHours(16),
        );

        $units = DB::table('units')
            ->join('properties', 'properties.id', '=', 'units.property_id')
            ->where('units.organization_id', $organizationId)
            ->get([
                'units.id',
                'units.name',
                'units.created_at',
                'properties.name as property_name',
            ]);

        foreach ($units as $unit) {
            $rows[] = $this->activityRow(
                ActivityEventEnum::UNIT_CREATED,
                'Unit created',
                "{$owner->name} created unit \"{$unit->name}\" at {$unit->property_name}.",
                $organizationId,
                $owner->id,
                Carbon::parse($unit->created_at),
                Unit::class,
                $unit->id,
            );
        }

        foreach ($units->whereIn('property_name', ['Riverside Commons', 'Westfield Residences'])->take(2) as $unit) {
            $rows[] = $this->activityRow(
                ActivityEventEnum::UNIT_UPDATED,
                'Unit updated',
                "{$owner->name} updated unit \"{$unit->name}\" at {$unit->property_name}.",
                $organizationId,
                $owner->id,
                now()->subDays(20)->startOfDay()->addMinutes($unit->id),
                Unit::class,
                $unit->id,
            );
        }

        $rows[] = $this->activityRow(
            ActivityEventEnum::UNIT_DELETED,
            'Unit deleted',
            "{$owner->name} deleted unit \"204\" from Cedar Grove Apartments.",
            $organizationId,
            $owner->id,
            now()->subDays(58)->startOfDay()->addHours(15),
        );

        foreach (DemoDataset::residents() as $residentData) {
            $resident = DB::table('users')->where('email', $residentData['email'])->first(['id', 'name', 'created_at']);
            $rows[] = $this->activityRow(
                ActivityEventEnum::RESIDENT_CREATED,
                'Resident created',
                "{$owner->name} created resident \"{$resident->name}\".",
                $organizationId,
                $owner->id,
                Carbon::parse($resident->created_at),
                User::class,
                $resident->id,
            );
        }

        foreach (DemoDataset::technicians() as $technicianData) {
            $technician = DB::table('users')->where('email', $technicianData['email'])->first(['id', 'name', 'created_at']);
            $rows[] = $this->activityRow(
                ActivityEventEnum::TECHNICIAN_CREATED,
                'Technician created',
                "{$owner->name} created technician \"{$technician->name}\".",
                $organizationId,
                $owner->id,
                Carbon::parse($technician->created_at),
                User::class,
                $technician->id,
            );
        }

        return $rows;
    }

    private function maintenanceActivityRows(int $organizationId, object $owner): array
    {
        $scenarios = DemoDataset::maintenanceRequests();
        $requests = MaintenanceRequest::query()
            ->where('organization_id', $organizationId)
            ->whereIn('title', array_column($scenarios, 'title'))
            ->with(['resident:id,name', 'assignedTechnician:id,name'])
            ->get()
            ->keyBy('title');
        $rows = [];

        foreach ($scenarios as $scenario) {
            $maintenanceRequest = $requests->get($scenario['title']);

            if ($maintenanceRequest === null) {
                throw new RuntimeException("Missing seeded maintenance request: {$scenario['title']}.");
            }

            foreach ($scenario['transitions'] as $transition) {
                [$title, $description, $actorId] = $this->maintenanceActivityContent(
                    $transition['activity_event'],
                    $maintenanceRequest,
                    $owner,
                );

                $rows[] = $this->activityRow(
                    $transition['activity_event'],
                    $title,
                    $description,
                    $organizationId,
                    $actorId,
                    $maintenanceRequest->created_at->copy()->addHours($transition['hours_after']),
                    MaintenanceRequest::class,
                    $maintenanceRequest->id,
                );
            }
        }

        return $rows;
    }

    private function maintenanceActivityContent(
        ActivityEventEnum $event,
        MaintenanceRequest $maintenanceRequest,
        object $owner,
    ): array {
        $resident = $maintenanceRequest->resident;
        $technician = $maintenanceRequest->assignedTechnician;

        return match ($event) {
            ActivityEventEnum::MAINTENANCE_REQUEST_CREATED => [
                'Maintenance request created',
                "Resident {$resident->name} created maintenance request \"{$maintenanceRequest->title}\".",
                $resident->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED => [
                'Technician assigned',
                "{$owner->name} assigned {$technician->name} to maintenance request \"{$maintenanceRequest->title}\".",
                $owner->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_STATUS_UPDATED => [
                'Maintenance request status updated',
                "{$owner->name} updated maintenance request \"{$maintenanceRequest->title}\" from Assigned to In Progress.",
                $owner->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_WORK_STARTED => [
                'Maintenance work started',
                "Technician {$technician->name} started work on maintenance request \"{$maintenanceRequest->title}\".",
                $technician->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_WORK_COMPLETED => [
                'Maintenance work completed',
                "Technician {$technician->name} completed maintenance request \"{$maintenanceRequest->title}\".",
                $technician->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED => [
                'Maintenance resolution confirmed',
                "Resident {$resident->name} confirmed maintenance request \"{$maintenanceRequest->title}\" as resolved.",
                $resident->id,
            ],
            ActivityEventEnum::MAINTENANCE_REQUEST_REOPENED => [
                'Maintenance request reopened',
                "Resident {$resident->name} reopened maintenance request \"{$maintenanceRequest->title}\".",
                $resident->id,
            ],
            default => throw new RuntimeException("Unsupported maintenance activity event: {$event->value}."),
        };
    }

    private function activityRow(
        ActivityEventEnum $event,
        string $title,
        string $description,
        int $organizationId,
        int $actorId,
        DateTimeInterface $createdAt,
        ?string $subjectType = null,
        ?int $subjectId = null,
    ): array {
        return [
            'organization_id' => $organizationId,
            'log_name' => 'organization',
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'event' => $event->value,
            'causer_type' => User::class,
            'causer_id' => $actorId,
            'attribute_changes' => null,
            'properties' => json_encode(['title' => $title], JSON_THROW_ON_ERROR),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
