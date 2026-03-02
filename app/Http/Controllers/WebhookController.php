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
            return response()->json([], 401);
        }

        $payload = json_decode($request->getContent(), true);

        $customerId = $payload['customer']['id'] ?? null;
        $shopDomain = $payload['shop_domain'] ?? null;

        Log::info("customers/redact", $payload);

        Shipment::where('shopify_customer_id', $customerId)
            ->update([
                'customer_name' => null,
                'email' => null,
                'phone' => null,
                'delivery_address' => null,
            ]);

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
        $shopId = $payload['shop_id'] ?? null;

        Log::info("shop/redact", $payload);

        // Delete all orders/bookings/shipments/customers tied to this shop
        Shipment::where('shop_domain', $shopDomain)->delete();
        Booking::where('shop_domain', $shopDomain)->delete();
        Customer::where('shop_domain', $shopDomain)->delete();

        User::where('shop_domain', $shopDomain)->delete();

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

        $customerId = $payload['customer']['id'] ?? null;
        $shopDomain = $payload['shop_domain'] ?? null;

        Log::info("customers/data_request", $payload);

        // Fetch all related data for this customer
        $shipments = Shipment::where('shopify_customer_id', $customerId)->get();

        return response()->json([
            'data' => $shipments
        ], 200);
    }
}
