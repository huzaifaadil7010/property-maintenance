<?php

use App\Mcp\Resident\ResidentIssueImageUpload;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('resident-mcp:prune-uploads', function () {
    ResidentIssueImageUpload::prune();
})->purpose('Remove expired resident MCP image uploads');

Schedule::command('resident-mcp:prune-uploads')->hourly();
