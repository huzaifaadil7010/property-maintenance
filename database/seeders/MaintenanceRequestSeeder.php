<?php

namespace Database\Seeders;

use App\Enums\MaintenanceRequestStatus;
use App\Models\MaintenanceRequest;
use Carbon\CarbonInterface;
use Database\Seeders\Support\DemoDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class MaintenanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = DB::table('organizations')
            ->where('slug', DemoDataset::ORGANIZATION_SLUG)
            ->value('id');

        if ($organizationId === null) {
            throw new RuntimeException('Run OwnerPortfolioSeeder before MaintenanceRequestSeeder.');
        }

        $scenarios = DemoDataset::maintenanceRequests();
        $this->ensureMediaAssetsExist($scenarios);
        $this->removeExistingScenarios($organizationId, $scenarios);
        $contexts = $this->seedRequestsAndStatusLogs($organizationId, $scenarios);
        $this->attachMedia($contexts);
    }

    private function removeExistingScenarios(int $organizationId, array $scenarios): void
    {
        $titles = array_column($scenarios, 'title');
        $existingRequests = MaintenanceRequest::query()
            ->where('organization_id', $organizationId)
            ->whereIn('title', $titles)
            ->get();

        foreach ($existingRequests as $maintenanceRequest) {
            $maintenanceRequest->clearMediaCollection(MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES);
            $maintenanceRequest->clearMediaCollection(MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES);
            $maintenanceRequest->delete();
        }
    }

    private function seedRequestsAndStatusLogs(int $organizationId, array $scenarios): array
    {
        return DB::transaction(function () use ($organizationId, $scenarios): array {
            $ownerId = DB::table('users')->where('email', DemoDataset::OWNER_EMAIL)->value('id');
            $usersByEmail = DB::table('users')
                ->where('current_organization_id', $organizationId)
                ->pluck('id', 'email');
            $occupanciesByResident = DB::table('occupancies')
                ->join('units', 'units.id', '=', 'occupancies.unit_id')
                ->where('occupancies.organization_id', $organizationId)
                ->get([
                    'occupancies.resident_id',
                    'occupancies.unit_id',
                    'units.property_id',
                ])
                ->keyBy('resident_id');
            $contexts = [];

            foreach ($scenarios as $scenarioIndex => $scenario) {
                $residentId = $usersByEmail->get($scenario['residentEmail']);
                $technicianId = $scenario['technicianEmail'] === null
                    ? null
                    : $usersByEmail->get($scenario['technicianEmail']);
                $occupancy = $occupanciesByResident->get($residentId);

                if ($residentId === null || $occupancy === null) {
                    throw new RuntimeException("Missing resident occupancy for {$scenario['residentEmail']}.");
                }

                if ($scenario['technicianEmail'] !== null && $technicianId === null) {
                    throw new RuntimeException("Missing technician {$scenario['technicianEmail']}.");
                }

                $createdAt = now()
                    ->subDays($scenario['createdDaysAgo'])
                    ->startOfDay()
                    ->addHours(8 + ($scenarioIndex % 5));
                $completedAt = $this->transitionTimestamp($scenario, MaintenanceRequestStatus::COMPLETED, $createdAt);
                $closedAt = $this->transitionTimestamp($scenario, MaintenanceRequestStatus::CLOSED, $createdAt);
                $lastTransition = collect($scenario['transitions'])->last();
                $updatedAt = $createdAt->copy()->addHours($lastTransition['hours_after']);

                $requestId = DB::table('maintenance_requests')->insertGetId([
                    'organization_id' => $organizationId,
                    'property_id' => $occupancy->property_id,
                    'unit_id' => $occupancy->unit_id,
                    'resident_id' => $residentId,
                    'assigned_technician_id' => $technicianId,
                    'title' => $scenario['title'],
                    'description' => $scenario['description'],
                    'category' => $scenario['category']->value,
                    'priority' => $scenario['priority']->value,
                    'status' => $scenario['status']->value,
                    'completion_notes' => $scenario['completionNotes'],
                    'actual_cost' => $scenario['actualCost'],
                    'completed_at' => $completedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $fromStatus = null;

                foreach ($scenario['transitions'] as $transition) {
                    DB::table('maintenance_request_status_logs')->insert([
                        'organization_id' => $organizationId,
                        'maintenance_request_id' => $requestId,
                        'changed_by' => match ($transition['actor']) {
                            'owner' => $ownerId,
                            'technician' => $technicianId,
                            default => $residentId,
                        },
                        'from_status' => $fromStatus?->value,
                        'to_status' => $transition['status']->value,
                        'notes' => $transition['notes'],
                        'created_at' => $createdAt->copy()->addHours($transition['hours_after']),
                    ]);

                    $fromStatus = $transition['status'];
                }

                $contexts[] = [
                    'request_id' => $requestId,
                    'created_at' => $createdAt,
                    'completed_at' => $completedAt,
                    'scenario' => $scenario,
                ];
            }

            return $contexts;
        });
    }

    private function attachMedia(array $contexts): void
    {
        $requests = MaintenanceRequest::query()
            ->whereIn('id', array_column($contexts, 'request_id'))
            ->get()
            ->keyBy('id');

        foreach ($contexts as $context) {
            $scenario = $context['scenario'];
            $maintenanceRequest = $requests->get($context['request_id']);
            $issueMedia = $maintenanceRequest
                ->addMedia($this->mediaAssetPath($scenario['category']->value, 'issue'))
                ->preservingOriginal()
                ->usingName($scenario['title'].' issue evidence')
                ->usingFileName(str($scenario['title'])->slug().'-issue.jpg')
                ->withCustomProperties([
                    'alt' => 'Issue evidence for '.$scenario['title'],
                    'fixture' => DemoDataset::ORGANIZATION_SLUG,
                ])
                ->toMediaCollection(MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES);

            $issueMedia->forceFill([
                'created_at' => $context['created_at']->copy()->addMinutes(15),
                'updated_at' => $context['created_at']->copy()->addMinutes(15),
            ])->save();

            if ($context['completed_at'] === null) {
                continue;
            }

            $completionMedia = $maintenanceRequest
                ->addMedia($this->mediaAssetPath($scenario['category']->value, 'completed'))
                ->preservingOriginal()
                ->usingName($scenario['title'].' completion evidence')
                ->usingFileName(str($scenario['title'])->slug().'-completed.jpg')
                ->withCustomProperties([
                    'alt' => 'Completion evidence for '.$scenario['title'],
                    'fixture' => DemoDataset::ORGANIZATION_SLUG,
                ])
                ->toMediaCollection(MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES);

            $completionMedia->forceFill([
                'created_at' => $context['completed_at']->copy()->addMinutes(15),
                'updated_at' => $context['completed_at']->copy()->addMinutes(15),
            ])->save();
        }
    }

    private function transitionTimestamp(
        array $scenario,
        MaintenanceRequestStatus $status,
        CarbonInterface $createdAt,
    ): ?CarbonInterface {
        $transition = collect($scenario['transitions'])
            ->first(fn (array $transition): bool => $transition['status'] === $status);

        return $transition === null
            ? null
            : $createdAt->copy()->addHours($transition['hours_after']);
    }

    private function ensureMediaAssetsExist(array $scenarios): void
    {
        foreach ($scenarios as $scenario) {
            $paths = [$this->mediaAssetPath($scenario['category']->value, 'issue')];

            if ($scenario['completionNotes'] !== null) {
                $paths[] = $this->mediaAssetPath($scenario['category']->value, 'completed');
            }

            foreach ($paths as $path) {
                if (! File::exists($path)) {
                    throw new RuntimeException("Missing maintenance seeder asset: {$path}");
                }
            }
        }
    }

    private function mediaAssetPath(string $category, string $state): string
    {
        return database_path("seeders/assets/maintenance/{$category}-{$state}.jpg");
    }
}
