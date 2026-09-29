<?php

use App\Mcp\Servers\OrganizationServer;
use App\Mcp\Servers\ResidentServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('/mcp/organization', OrganizationServer::class);
Mcp::local('/mcp/resident', ResidentServer::class);
