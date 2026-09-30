<?php

namespace App\Mcp\Resident;

use App\Actions\Resident\CreateMaintenanceRequest;
use App\Actions\Resident\Dashboard\GetCurrentResidence;
use App\Actions\Resident\MaintenanceRequest\ConfirmMaintenanceRequestResolution;
use App\Actions\Resident\MaintenanceRequest\ReopenMaintenanceRequest;
use App\Data\CreateResidentMaintenanceRequestChangeData;
use App\Data\MaintenanceRequestData;
use App\Data\MaintenanceRequestStatusData;
use App\Data\ResidentChangeInputData;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\ResidentChangeOperationEnum;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\User;
use App\Validation\ResidentInputRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResidentChange
{
    private const int TOKEN_LIFETIME_MINUTES = 5;

    public static function prepare(ResidentChangeOperationEnum $operation, ResidentChangeInputData $input, User $resident, Organization $organization): array
    {
        if (! $operation->accepts($input)) {
            throw ValidationException::withMessages(['operation' => 'The resident change input does not match the selected operation.']);
        }

        self::validate($operation, $input, $resident);

        $impact = self::impact($operation, $input, $resident);
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::TOKEN_LIFETIME_MINUTES);

        Cache::put(self::cacheKey($token), [
            'resident_id' => $resident->id,
            'organization_id' => $organization->id,
            'operation' => $operation->value,
            'data' => $input->toArray(),
            'impact' => $impact,
        ], $expiresAt);

        return [
            'operation' => $operation->value,
            'summary' => self::summary($operation, $input),
            'impact' => $impact,
            'confirmation_token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => self::TOKEN_LIFETIME_MINUTES * 60,
            'requires_client_approval' => true,
        ];
    }

    public static function confirm(string $token, User $resident, Organization $organization): array
    {
        return Cache::lock('resident-mcp:confirmation-lock:'.$token, 10)->block(3, function () use ($token, $resident, $organization): array {
            $prepared = Cache::pull(self::cacheKey($token));

            if (! is_array($prepared)
                || ($prepared['resident_id'] ?? null) !== $resident->id
                || ($prepared['organization_id'] ?? null) !== $organization->id
                || ! is_array($prepared['data'] ?? null)) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation has expired or was already used. Prepare the change again.']);
            }

            $operation = ResidentChangeOperationEnum::tryFrom($prepared['operation'] ?? '');

            if ($operation === null) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation is invalid. Prepare the change again.']);
            }

            $inputDataClass = $operation->inputDataClass();
            $input = $inputDataClass::validateAndCreate($prepared['data']);

            if (! $operation->accepts($input)) {
                throw ValidationException::withMessages(['confirmation_token' => 'This confirmation is invalid. Prepare the change again.']);
            }

            self::validate($operation, $input, $resident);

            if (self::impact($operation, $input, $resident) !== $prepared['impact']) {
                throw ValidationException::withMessages(['confirmation_token' => 'The residence, request, or images changed. Prepare the change again.']);
            }

            $result = self::execute($operation, $input, $resident);

            Log::info('Resident MCP change confirmed', [
                'operation' => $operation->value,
                'resident_id' => $resident->id,
                'organization_id' => $organization->id,
                'request_id' => $result->id,
            ]);

            return ['operation' => $operation->value, 'result' => ['id' => $result->id, 'status' => $result->status->value]];
        });
    }

    private static function cacheKey(string $token): string
    {
        return 'resident-mcp:confirmation:'.$token;
    }

    private static function validate(ResidentChangeOperationEnum $operation, ResidentChangeInputData $input, User $resident): void
    {
        $rules = match ($operation) {
            ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST => [
                ...ResidentInputRules::createMaintenanceRequest(),
                'images.*' => ['required', 'uuid', 'distinct'],
            ],
            ResidentChangeOperationEnum::CONFIRM_RESOLUTION => [
                'id' => ['required', 'integer', 'min:1'],
                ...ResidentInputRules::confirmResolutionNotes(),
            ],
            ResidentChangeOperationEnum::REOPEN_MAINTENANCE_REQUEST => [
                'id' => ['required', 'integer', 'min:1'],
                ...ResidentInputRules::reopenNotes(),
            ],
        };

        Validator::make($input->toArray(), $rules)->validate();

        if ($operation === ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST) {
            if (GetCurrentResidence::handle($resident) === null) {
                throw ValidationException::withMessages(['cannot_submit' => 'An active residence is required before reporting an issue.']);
            }

            ResidentIssueImageUpload::inspect($input->images, $resident);

            return;
        }

        $request = self::ownedRequest($input->id, $resident);

        if (! $request->isCompleted()) {
            throw ValidationException::withMessages(['cannot_submit' => 'Only completed requests can be confirmed or reopened.']);
        }
    }

    private static function impact(ResidentChangeOperationEnum $operation, ResidentChangeInputData $input, User $resident): array
    {
        if ($operation === ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST) {
            $residence = GetCurrentResidence::handle($resident);

            return [
                'occupancy_id' => $residence->id,
                'unit_id' => $residence->unit_id,
                'images' => ResidentIssueImageUpload::inspect($input->images, $resident),
                'notifies_organization_owner' => true,
            ];
        }

        $request = self::ownedRequest($input->id, $resident);

        return [
            'request_id' => $request->id,
            'current_status' => $request->status->value,
            'target_updated_at' => (string) $request->updated_at,
            'new_status' => $operation === ResidentChangeOperationEnum::CONFIRM_RESOLUTION
                ? MaintenanceRequestStatus::CLOSED->value
                : MaintenanceRequestStatus::REOPENED->value,
        ];
    }

    private static function summary(ResidentChangeOperationEnum $operation, ResidentChangeInputData $input): string
    {
        return match ($operation) {
            ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST => 'Report maintenance request "'.$input->title.'" with '.count($input->images).' issue image(s)',
            ResidentChangeOperationEnum::CONFIRM_RESOLUTION => 'Confirm maintenance request #'.$input->id.' as resolved',
            ResidentChangeOperationEnum::REOPEN_MAINTENANCE_REQUEST => 'Reopen maintenance request #'.$input->id.' with reason: '.$input->notes,
        };
    }

    private static function execute(ResidentChangeOperationEnum $operation, ResidentChangeInputData $input, User $resident): MaintenanceRequest
    {
        if ($operation === ResidentChangeOperationEnum::CREATE_MAINTENANCE_REQUEST) {
            return self::create($input, $resident);
        }

        $request = self::ownedRequest($input->id, $resident);
        $status = $operation === ResidentChangeOperationEnum::CONFIRM_RESOLUTION
            ? MaintenanceRequestStatus::CLOSED
            : MaintenanceRequestStatus::REOPENED;
        $data = new MaintenanceRequestStatusData($status, $input->notes);

        return match ($operation) {
            ResidentChangeOperationEnum::CONFIRM_RESOLUTION => ConfirmMaintenanceRequestResolution::handle($request, $data, $resident),
            ResidentChangeOperationEnum::REOPEN_MAINTENANCE_REQUEST => ReopenMaintenanceRequest::handle($request, $data, $resident),
            default => throw ValidationException::withMessages(['operation' => 'Unsupported resident change.']),
        };
    }

    private static function create(CreateResidentMaintenanceRequestChangeData $input, User $resident): MaintenanceRequest
    {
        return CreateMaintenanceRequest::handle(new MaintenanceRequestData(
            $input->title,
            $input->category,
            $input->priority,
            $input->description,
            $input->images,
        ), $resident);
    }

    private static function ownedRequest(int $id, User $resident): MaintenanceRequest
    {
        return MaintenanceRequest::query()->where('resident_id', $resident->id)->findOrFail($id);
    }
}
