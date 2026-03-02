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
    public function uninstallApp(Request $request)
    {
        // Log the incoming webhook to verify it's received
        Log::info('APP_UNINSTALLED Webhook received.', ['payload' => $request->all()]);

        $shopDomain = $request->input('domain');

        if (empty($shopDomain)) {
            return response('Invalid request payload. Missing shop domain.', 400);
        }

        $shop = User::where('name', $shopDomain)->first();

        if (!$shop) {
            return response('Shop not found.', 404);
        }

        try {
            // Here you would add your logic to delete the shop's data.
            // The $shop->delete() call is correct for deleting the shop record.
            $shop->delete();
            Log::info("Shop record deleted for domain: {$shopDomain}. App uninstalled.");

            return response('Shop data deleted successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Error during app uninstallation.', [
                'shop_domain' => $shopDomain,
                'error' => $e->getMessage(),
            ]);
            return response('An error occurred during uninstallation.', 500);
        }
    }

    private function verifyWebhook(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        $calculatedHmac = base64_encode(
            hash_hmac('sha256', $data, config('services.shopify.secret'), true)
        );

        return hash_equals($hmacHeader, $calculatedHmac);
    }
    /**
     * Handle the customers/redact webhook.
     */
    public function customerRedact(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        $customerId = $payload['customer']['id'] ?? null;
        $shopDomain = $payload['shop_domain'] ?? null;

        if (!$customerId || !$shopDomain) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info("customers/redact received", [
            'customer_id' => $customerId,
            'shop' => $shopDomain
        ]);

        // Anonymize personal data
        Shipment::where('shopify_customer_id', $customerId)
            ->update([
                'customer_name' => null,
                'email' => null,
                'phone' => null,
                'delivery_address' => null,
            ]);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle the shop/redact webhook.
     */

    public function shopRedact(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        $shopId = $payload['shop_id'] ?? null;
        $shopDomain = $payload['shop_domain'] ?? null;

        if (!$shopId || !$shopDomain) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info("shop/redact received", [
            'shop_id' => $shopId,
            'shop_domain' => $shopDomain
        ]);

        try {
            // Delete related records
            Shop::where('shop_domain', $shopId)->delete();

            // Delete local shop/user record
            $user = User::where('shop_domain', $shopDomain)->first();

            if ($user) {
                $user->delete();
            }

            Log::info("Shop data fully redacted", [
                'shop_id' => $shopId
            ]);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error("Error during shop redaction", [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Handle the customers/data_request webhook.
     */
    public function customerDataRequest(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        $customerId = $payload['customer']['id'] ?? null;
        $shopDomain = $payload['shop_domain'] ?? null;

        if (!$customerId || !$shopDomain) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info("customers/data_request received", [
            'customer_id' => $customerId,
            'shop' => $shopDomain
        ]);

        $shipments = Shipment::where('shopify_customer_id', $customerId)->get();

        Log::info("Customer data prepared", [
            'count' => $shipments->count()
        ]);

        // Shopify expects 200 response
        return response()->json(['status' => 'success'], 200);
    }
}
