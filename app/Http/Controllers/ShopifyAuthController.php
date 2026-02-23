<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
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
            $shop = $request->shop;
            $code = $request->code;

            if (!$shop || !$code) {
                return response()->json([
                    'error' => 'Invalid OAuth response'
                ], 400);
            }

            // 🔐 Exchange code for access token
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => config('services.shopify.key'),
                'client_secret' => config('services.shopify.secret'),
                'code' => $code,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to retrieve access token'
                ], 500);
            }

            $accessToken = $response->json()['access_token'];

            // 🏬 Store or update shop in DB
            $shopRecord = Shop::updateOrCreate(
                ['shop_domain' => $shop],
                [
                    'shopify_access_token' => $accessToken
                ]
            );

            /*
            If shop was already linked to a user,
            we DO NOT unlink it.
            We only update the token (reinstall case).
            */

            // 🚀 Redirect back to React
            return redirect("https://scs-green-pi.vercel.app/?shop={$shop}");
        }

}