<?php

namespace AicodesDeveloper\Chargebee\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use AicodesDeveloper\Chargebee\Facades\Chargebee;

class WebhookController extends Controller
{
    /**
     * Handle the incoming webhook.
     */
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Chargebee-Signature');
        
        try {
            $eventData = Chargebee::handleWebhook($payload, $signature);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}