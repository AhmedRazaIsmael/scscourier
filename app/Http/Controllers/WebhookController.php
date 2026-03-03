<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private function verifyWebhook(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $secret = config('services.shopify.secret');

        $calculatedHmac = base64_encode(
            hash_hmac('sha256', $data, $secret, true)
        );

        Log::info('HMAC DEBUG', [
            'header' => $hmacHeader,
            'calculated' => $calculatedHmac,
            'raw_body' => $data,
        ]);

        return hash_equals($hmacHeader ?? '', $calculatedHmac);
    }

    public function uninstallApp(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response('Unauthorized', 401);
        }

        $payload = json_decode($request->getContent(), true);

        $shopDomain = $payload['myshopify_domain'] ?? null;

        Log::info('app/uninstalled received', $payload);

        if ($shopDomain) {
            User::where('shop_domain', $shopDomain)->delete();
        }

        return response('OK', 200);
    }

    
    /**
     * Handle the customers/redact webhook.
     */
    public function customerRedact(Request $request)
    {
        dd('Webhook reached controller');
        if (!$this->verifyWebhook($request)) {
            return response()->json([], 401);
        }

        $payload = json_decode($request->getContent(), true);

        Log::info('customers/redact received', $payload);

        // You do not store Shopify customer PII tied to Shopify ID
        // So nothing to delete

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle the shop/redact webhook.
     */

    public function shopRedact(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json([], 401);
        }

        $payload = json_decode($request->getContent(), true);

        $shopDomain = $payload['shop_domain'] ?? null;

        Log::info('shop/redact received', $payload);

        if ($shopDomain) {
            User::where('shop_domain', $shopDomain)->delete();
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Handle the customers/data_request webhook.
     */
    public function customerDataRequest(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json([], 401);
        }

        $payload = json_decode($request->getContent(), true);

        Log::info('customers/data_request received', $payload);

        // You do not store Shopify customer data
        // So return empty response as required by Shopify

        return response()->json([
            'data' => []
        ], 200);
    }
}
