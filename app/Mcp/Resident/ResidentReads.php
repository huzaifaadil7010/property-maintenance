<?php

namespace App\Mcp\Resident;

use App\Actions\Resident\Dashboard\GetCurrentResidence;
use App\Actions\Resident\Dashboard\GetRecentMaintenanceRequests;
use App\Actions\Resident\Dashboard\GetTotalMaintenanceRequestsByStatus;
use App\Actions\Resident\GetMyMaintenanceRequests;
use App\Actions\Resident\MaintenanceRequest\GetMyMaintenanceRequest;
use App\Data\MaintenanceRequestFilterData;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Http\Resources\ResidentMaintenanceRequestDetailResource;
use App\Http\Resources\ResidentMaintenanceRequestResource;
use App\Http\Resources\ResidentResidenceResource;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResidentReads
{
    public static function dashboard(User $resident): array
    {
        $residence = GetCurrentResidence::handle($resident);

        return [
            'residence' => $residence ? (new ResidentResidenceResource($residence))->resolve() : null,
            'recent_maintenance_requests' => GetRecentMaintenanceRequests::handle($resident)
                ->map(fn (MaintenanceRequest $request): array => (new ResidentMaintenanceRequestResource($request))->resolve())->all(),
            'total_open_requests' => GetTotalMaintenanceRequestsByStatus::handle([MaintenanceRequestStatus::OPEN, MaintenanceRequestStatus::ASSIGNED], $resident),
            'total_in_progress_requests' => GetTotalMaintenanceRequestsByStatus::handle([MaintenanceRequestStatus::IN_PROGRESS, MaintenanceRequestStatus::REOPENED], $resident),
            'total_completed_requests' => GetTotalMaintenanceRequestsByStatus::handle([MaintenanceRequestStatus::COMPLETED, MaintenanceRequestStatus::CLOSED], $resident),
        ];
    }

    public static function listing(array $input, User $resident): array
    {
        $data = Validator::make($input, [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(MaintenanceRequestStatus::class)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 30, 40, 50])],
        ])->validate();

        $paginator = GetMyMaintenanceRequests::handle(MaintenanceRequestFilterData::from([
            'search' => $data['search'] ?? null,
            'status' => $data['status'] ?? null,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
            'page' => $data['page'] ?? null,
            'perPage' => $data['per_page'] ?? null,
        ]), $resident);

        return [
            'data' => $paginator->getCollection()->map(fn (MaintenanceRequest $request): array => (new ResidentMaintenanceRequestResource($request))->resolve())->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public static function detail(int $id, User $resident): array
    {
        $request = MaintenanceRequest::query()->where('resident_id', $resident->id)->findOrFail($id);

        return (new ResidentMaintenanceRequestDetailResource(GetMyMaintenanceRequest::handle($request, $resident)))->resolve();
    }

    public static function options(): array
    {
        return [
            'maintenance_categories' => MaintenanceCategory::getLabeledValues(),
            'maintenance_priorities' => MaintenancePriority::getLabeledValues(),
        ];
    }
}
