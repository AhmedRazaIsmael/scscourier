<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\CustomerShop;
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
    /**
     * Handle the customers/redact webhook.
     */
    public function customerRedact(Request $request)
    {
        $payload = $request->processWebhook(['webhook' => 'customers/redact']);

        $shopifyCustomerId = $payload['customer']['id'];
        $shopDomain = $payload['shop_domain'];

        Log::info("Received customers/redact webhook for customer_id: {$shopifyCustomerId} on shop: {$shopDomain}");

        // Anonymize personal data in your shipments table
        Shipment::where('shopify_customer_id', $shopifyCustomerId)
            ->update([
                'customer_name' => '',
                'email' => '',
                'phone' => '',
                'delivery_address' => '',
            ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle the shop/redact webhook.
     */

    public function shopRedact(Request $request)
    {
        // Log the incoming webhook for debugging
        Log::info('Shop Redact Webhook received.', ['payload' => $request->all()]);

        // Validate the request body
        $validatedData = $request->validate([
            'shop_id' => 'required|numeric',
            'shop_domain' => 'required|string',
        ]);

        $shopId = $validatedData['shop_id'];
        $shopDomain = $validatedData['shop_domain'];


        // Find the shop record in our database
        // $shop = Shop::where('id', $shopId)->first();
        $shop = User::where('name', $shopDomain)->first();
        // if not found then return 401 unauthorized
        if (!$shop) {
            return response('Shop not found.', 401);
        }
        // if ($shop) {
        // Log::info("Found shop to redact: {$shop->name}. Starting data deletion.");

        try {
            // Shipment::where('shop_id', $shopId)->delete();
            // Log::info("Deleted shipments for shop_id: {$shopId}");

            CustomerShop::where('shopify_shop_id', $shopId)->delete();
            Log::info("Deleted customers records for shop_id: {$shopId}");
            Log::info("Shop record deleted for shop_id: {$shop->id}. Redaction complete.");
            $shop->delete();


            return response('Data redacted successfully.', 200);
        } catch (\Exception $e) {
            // Log any errors that occur during the deletion process
            Log::error('Error during shop redaction.', [
                'shop_id' => $shopId,
                'error' => $e->getMessage(),
            ]);
            // Return a server error response
            return response('An error occurred during redaction.', 500);
        }
        // } else {
        //     Log::warning('Shop not found for redaction.', ['shop_id' => $shopId]);
        //     // Return a not found response if the shop ID doesn't exist
        //     return response('Shop not found.', 404);
        // }
    }

    /**
     * Handle the customers/data_request webhook.
     */
    public function customerDataRequest(Request $request)
    {
        $payload = $request->processWebhook(['webhook' => 'customers/data_request']);

        $shopifyCustomerId = $payload['customer']['id'];
        $shopDomain = $payload['shop_domain'];
        Log::info("Received customers/data_request webhook for customer_id: {$shopifyCustomerId} on shop: {$shopDomain}");

        // Find all data for this customer in your shipments table
        $shipments = Shipment::where('shopify_customer_id', $shopifyCustomerId)->get();

        // Format the data and prepare for sending to Shopify's privacy API
        Log::info("Customer data found: ", ['data' => $shipments->toArray()]);

        return response()->json(['status' => 'success']);
    }
}
