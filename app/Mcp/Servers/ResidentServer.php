<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\ResidentIssueImagePicker;
use App\Mcp\Tools\Resident\ConfirmResidentChange;
use App\Mcp\Tools\Resident\DiscardResidentIssueImages;
use App\Mcp\Tools\Resident\GetResidentDashboardData;
use App\Mcp\Tools\Resident\GetResidentMaintenanceOptions;
use App\Mcp\Tools\Resident\GetResidentMaintenanceRequest;
use App\Mcp\Tools\Resident\GetResidentRequestImage;
use App\Mcp\Tools\Resident\ListResidentMaintenanceRequests;
use App\Mcp\Tools\Resident\OpenResidentIssueImagePicker;
use App\Mcp\Tools\Resident\PrepareConfirmResidentResolution;
use App\Mcp\Tools\Resident\PrepareCreateResidentMaintenanceRequest;
use App\Mcp\Tools\Resident\PrepareReopenResidentMaintenanceRequest;
use App\Mcp\Tools\Resident\UploadResidentIssueImageChunk;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tools\ToolSearch;
use Laravel\Mcp\Server\Transport\StdioTransport;

#[Name('Resident Server')]
#[Version('0.0.1')]
#[Instructions('Manage only the configured local resident\'s own maintenance requests. Read tools do not change data. Every final write requires a prepare tool, user review of its summary and impact, then confirm-resident-change with the one-time token. Never confirm a change without the user approving its preview. To create a request with images, open-resident-issue-image-picker lets the user select files in chat and approve the preview; never request server-side inbox filenames.')]
class ResidentServer extends Server
{
    public function start(): void
    {
        parent::start();

        if (! $this->transport instanceof StdioTransport) {
            return;
        }

        $buffer = '';

        // Laravel MCP's nonblocking stdio reader may deliver one JSON line in several chunks.
        $this->transport->onReceive(function (string $chunk) use (&$buffer): void {
            $buffer .= $chunk;

            while (($newline = strpos($buffer, "\n")) !== false) {
                $message = substr($buffer, 0, $newline + 1);
                $buffer = substr($buffer, $newline + 1);

                $this->handle($message);
            }
        });
    }

    protected array $tools = [
        OpenResidentIssueImagePicker::class,
        UploadResidentIssueImageChunk::class,
        DiscardResidentIssueImages::class,
        ToolSearch::class => [
            GetResidentDashboardData::class,
            ListResidentMaintenanceRequests::class,
            GetResidentMaintenanceRequest::class,
            GetResidentMaintenanceOptions::class,
            GetResidentRequestImage::class,
            PrepareCreateResidentMaintenanceRequest::class,
            PrepareConfirmResidentResolution::class,
            PrepareReopenResidentMaintenanceRequest::class,
            ConfirmResidentChange::class,
        ],
    ];

    protected array $resources = [
        ResidentIssueImagePicker::class,
    ];
}
