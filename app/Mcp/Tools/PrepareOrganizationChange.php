<?php

namespace App\Mcp\Tools;

use App\Enums\OrganizationChangeOperationEnum;
use App\Mcp\Organization\OrganizationChange;
use App\Mcp\Organization\OwnerContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

abstract class PrepareOrganizationChange extends Tool
{
    protected OrganizationChangeOperationEnum $operation;

    public function handle(Request $request): ResponseFactory
    {
        return OwnerContext::run(
            function ($owner, $organization) use ($request): ResponseFactory {
                $inputDataClass = $this->operation->inputDataClass();
                $input = $inputDataClass::validateAndCreate($request->all());

                return Response::structured(
                    OrganizationChange::prepare($this->operation, $input, $owner, $organization),
                );
            },
        );
    }
}
