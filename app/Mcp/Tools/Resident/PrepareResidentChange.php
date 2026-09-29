<?php

namespace App\Mcp\Tools\Resident;

use App\Enums\ResidentChangeOperationEnum;
use App\Mcp\Resident\ResidentChange;
use App\Mcp\Resident\ResidentContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

abstract class PrepareResidentChange extends Tool
{
    protected ResidentChangeOperationEnum $operation;

    public function handle(Request $request): ResponseFactory
    {
        return ResidentContext::run(function ($resident, $organization) use ($request): ResponseFactory {
            $inputDataClass = $this->operation->inputDataClass();
            $input = $inputDataClass::validateAndCreate($request->all());

            return Response::structured(ResidentChange::prepare($this->operation, $input, $resident, $organization));
        });
    }
}
