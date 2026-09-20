<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class CreateSubscriptionIntentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $organization = $request->user()->currentOrganization;

        try {
            $organization->createOrGetStripeCustomer();
            $intent = $organization->createSetupIntent(['payment_method_types' => ['card']]);

            return response()->json(['client_secret' => $intent->client_secret]);
        } catch (ApiErrorException $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to initialize secure card entry.'], 502);
        }
    }
}
