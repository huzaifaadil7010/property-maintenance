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
class DiscardResidentIssueImages extends Tool
{
    protected string $description = 'Discard staged issue images removed or cancelled in the resident picker.';

    public function schema(JsonSchema $schema): array
    {
        return ['images' => $schema->array()->items($schema->string())->required()];
    }

    public function handle(Request $request): ResponseFactory
    {
        $validatedDiscardInput = $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'uuid', 'distinct'],
        ]);

        return ResidentContext::run(function ($resident) use ($validatedDiscardInput): ResponseFactory {
            ResidentIssueImageUpload::discard($validatedDiscardInput['images'], $resident);

            return Response::structured(['discarded' => true]);
        });
    }
}
