<?php

namespace App\Mcp\Tools\Resident;

use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentIssueImageUpload;
use App\Mcp\Resources\ResidentIssueImagePicker;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Ui\Enums\Visibility;

#[RendersApp(resource: ResidentIssueImagePicker::class, visibility: [Visibility::App])]
class UploadResidentIssueImageChunk extends Tool
{
    protected string $description = 'Upload a bounded image chunk from the resident issue picker.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'upload_id' => $schema->string()->description('Upload ID returned by the first chunk; omit for chunk zero.'),
            'name' => $schema->string()->required(),
            'size' => $schema->integer()->required(),
            'sha256' => $schema->string()->required(),
            'total_chunks' => $schema->integer()->required(),
            'chunk_index' => $schema->integer()->required(),
            'data' => $schema->string()->description('Base64-encoded chunk, at most 512 KiB decoded.')->required(),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        $validatedChunkInput = $request->validate([
            'upload_id' => ['sometimes', 'uuid'],
            'name' => ['required', 'string', 'max:255', 'not_regex:/[\\\\\/\x00-\x1F]/'],
            'size' => ['required', 'integer', 'between:1,10485760'],
            'sha256' => ['required', 'regex:/\A[a-fA-F0-9]{64}\z/'],
            'total_chunks' => ['required', 'integer', 'between:1,20'],
            'chunk_index' => ['required', 'integer', 'between:0,19'],
            'data' => ['required', 'string', 'max:700000'],
        ]);

        return ResidentContext::run(fn ($resident): ResponseFactory => Response::structured(
            ResidentIssueImageUpload::upload($validatedChunkInput, $resident),
        ));
    }
}
