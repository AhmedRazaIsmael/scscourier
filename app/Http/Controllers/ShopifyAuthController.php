<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Shop;

class ShopifyAuthController extends Controller

{

    //

    public function redirectToShopify(Request $request)

    {

        $shop = $request->shop;

        $scopes = env('SHOPIFY_API_SCOPES');

        $redirectUri = route('shopify.callback');

        $installUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([

            'client_id' => config('services.shopify.key'),

            'scope' => $scopes,

            'redirect_uri' => $redirectUri,

        ]);

        // App Bridge / SPA: return JSON so frontend can redirectToUrl() — avoids CORS (no fetch following 302)

        if ($request->query('format') === 'json' || $request->wantsJson()) {

            return response()->json(['redirectUrl' => $installUrl]);

        }

        return redirect($installUrl);

    }

        public function handleCallback(Request $request)
        {
            // 🔎 Log incoming request for debugging (optional but useful)
            Log::info('Shopify OAuth Callback', $request->all());

            $shop = $request->query('shop');
            $code = $request->query('code');
            $hmac = $request->query('hmac');

            // ✅ Basic validation
            if (!$shop || !$code || !$hmac) {
                return response()->json([
                    'error' => 'Invalid OAuth response parameters'
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | 🔐 Step 1 — Validate HMAC (VERY IMPORTANT)
            |--------------------------------------------------------------------------
            */
            $queryParams = $request->query();
            unset($queryParams['hmac']);

            ksort($queryParams);

            $calculatedHmac = hash_hmac(
                'sha256',
                urldecode(http_build_query($queryParams)),
                config('services.shopify.secret')
            );

            if (!hash_equals($hmac, $calculatedHmac)) {
                return response()->json([
                    'error' => 'Invalid HMAC validation'
                ], 403);
            }

            /*
            |--------------------------------------------------------------------------
            | 🔁 Step 2 — Exchange Code For Access Token
            |--------------------------------------------------------------------------
            */
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => config('services.shopify.key'),
                'client_secret' => config('services.shopify.secret'),
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::error('Shopify token exchange failed', [
                    'shop' => $shop,
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to retrieve access token from Shopify'
                ], 500);
            }

            $accessToken = $response->json()['access_token'] ?? null;

            if (!$accessToken) {
                return response()->json([
                    'error' => 'Access token missing in Shopify response'
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | 🏬 Step 3 — Store or Update Shop Record
            |--------------------------------------------------------------------------
            */
            $shopRecord = Shop::updateOrCreate(
                ['shop_domain' => $shop],
                [
                    'shopify_access_token' => $accessToken
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 🚀 Step 4 — Redirect Back To React App
            |--------------------------------------------------------------------------
            */

            return redirect("https://scs-green-pi.vercel.app/?shop={$shop}");
        }

}