<?php

namespace App\Mcp\Tools\Resident;

use App\Actions\Resident\Dashboard\GetCurrentResidence;
use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentReads;
use App\Mcp\Resources\ResidentIssueImagePicker;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[RendersApp(resource: ResidentIssueImagePicker::class)]
class OpenResidentIssueImagePicker extends Tool
{
    protected string $description = 'Open an in-chat file picker to report a resident maintenance issue with 1–10 images. Prefill the details from the conversation; the resident chooses local images and approves the final preview in the app. Do not ask them to put files in a server folder.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Suggested issue title.'),
            'category' => $schema->string()->description('Suggested maintenance category value.'),
            'priority' => $schema->string()->description('Suggested priority value.'),
            'description' => $schema->string()->description('Suggested issue description.'),
        ];
    }

    public function handle(Request $request): ResponseFactory
    {
        return ResidentContext::run(function ($resident) use ($request): ResponseFactory {
            if (GetCurrentResidence::handle($resident) === null) {
                throw ValidationException::withMessages(['cannot_submit' => 'An active residence is required before reporting an issue.']);
            }

            $fields = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string'],
                'priority' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ]);

            return Response::structured([
                'fields' => $fields,
                'options' => ResidentReads::options(),
                'message' => 'Choose 1–10 images in the picker, review the preview, then approve creation.',
            ]);
        });
    }
}
