<?php

namespace App\Mcp\Resources;

use App\Mcp\Resident\ResidentContext;
use App\Mcp\Resident\ResidentReads;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;

class ResidentIssueImagePicker extends AppResource
{
    protected string $name = 'Resident issue image picker';

    protected string $description = 'Select images, review a resident maintenance request, and approve its creation.';

    public function resolvedAppMeta(): array
    {
        return $this->appMeta()->toArray();
    }

    public function handle(Request $request): Response
    {
        return ResidentContext::run(fn (): Response => Response::view('mcp.resident-issue-image-picker', [
            'options' => ResidentReads::options(),
        ]));
    }
}
