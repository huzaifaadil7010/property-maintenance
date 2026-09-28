<?php

use App\Mcp\Servers\OrganizationServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('/mcp/organization', OrganizationServer::class);
