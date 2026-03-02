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

        if (!$hmacHeader) {
            return false;
        }

        $data = $request->getContent();
        $secret = config('services.shopify.secret');

        $calculatedHmac = base64_encode(
            hash_hmac('sha256', $data, $secret, true)
        );

        return hash_equals($hmacHeader, $calculatedHmac);
    }

    public function uninstallApp(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response('Unauthorized', 401);
        }

        Log::info('APP_UNINSTALLED Webhook received.', [
            'payload' => $request->getContent()
        ]);

        $payload = json_decode($request->getContent(), true);
        $shopDomain = $payload['domain'] ?? $payload['myshopify_domain'] ?? null;

        if (empty($shopDomain)) {
            return response('Invalid request payload. Missing shop domain.', 400);
        }

        $shop = User::where('name', $shopDomain)->first();

        if (!$shop) {
            return response('Shop not found.', 404);
        }

        try {
            $shop->delete();

            Log::info("Shop record deleted for domain: {$shopDomain}");

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error during app uninstallation.', [
                'shop_domain' => $shopDomain,
                'error' => $e->getMessage(),
            ]);

            return response('Server error', 500);
        }
    }

    
    /**
     * Handle the customers/redact webhook.
     */
    public function customerRedact(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json([], 401);
        }

        $payload = json_decode($request->getContent(), true);

        Log::info("customers/redact received", $payload);

        // If you do NOT store Shopify customer data, just return 200

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

        Log::info("shop/redact received", $payload);

        if ($shopDomain) {

            // Delete users
            User::where('shop_domain', $shopDomain)->delete();

            // If customers and bookings are tied to shop, delete them too
            Customer::where('shop_domain', $shopDomain)->delete();
            Booking::where('shop_domain', $shopDomain)->delete();
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

        Log::info("customers/data_request received", $payload);

        return response()->json([
            'data' => []
        ], 200);
    }
}
