<?php

use App\Http\Middleware\AuthenticateLocalMcpOwner;
use App\Mcp\Servers\OrganizationServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/organization', OrganizationServer::class)
    ->middleware(AuthenticateLocalMcpOwner::class);
