<?php

namespace App\Mcp\Tools\Resident;

use App\Actions\Resident\MaintenanceRequest\GetMyMaintenanceRequest;
use App\Mcp\Resident\ResidentContext;
use App\Models\MaintenanceRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

#[IsReadOnly]
class GetResidentRequestImage extends Tool
{
    protected string $description = 'View a bounded visual preview of an issue or completion image on this resident\'s own maintenance request. Use IDs from get-resident-maintenance-request.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'request_id' => $schema->integer()->required(),
            'attachment_id' => $schema->integer()->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $input = $request->validate([
            'request_id' => ['required', 'integer', 'min:1'],
            'attachment_id' => ['required', 'integer', 'min:1'],
        ]);

        return ResidentContext::run(function ($resident) use ($input): Response {
            $owned = MaintenanceRequest::query()->where('resident_id', $resident->id)->findOrFail($input['request_id']);
            $owned = GetMyMaintenanceRequest::handle($owned, $resident);
            $media = $owned->media()
                ->whereKey($input['attachment_id'])
                ->whereIn('collection_name', [MaintenanceRequest::MEDIA_COLLECTION_ISSUE_IMAGES, MaintenanceRequest::MEDIA_COLLECTION_COMPLETION_IMAGES])
                ->firstOrFail();

            $temporaryPath = tempnam(sys_get_temp_dir(), 'resident-mcp-image-');

            if ($temporaryPath === false) {
                throw ValidationException::withMessages(['attachment_id' => 'Unable to create a temporary image preview.']);
            }

            try {
                foreach ([384 => 65, 300 => 55, 240 => 45, 160 => 35] as $width => $quality) {
                    Image::load($media->getPath())->fit(Fit::Max, $width, $width)->format('jpg')->quality($quality)->save($temporaryPath);

                    if (filesize($temporaryPath) <= 32 * 1024) {
                        return Response::image(file_get_contents($temporaryPath), 'image/jpeg');
                    }
                }
            } finally {
                unlink($temporaryPath);
            }

            throw ValidationException::withMessages(['attachment_id' => 'This image cannot be reduced to a safe MCP preview size.']);
        });
    }
}
