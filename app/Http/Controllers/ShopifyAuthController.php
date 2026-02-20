<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

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

        return redirect($installUrl);
    }

        public function handleCallback(Request $request)
        {
            $shop = $request->shop;
            $code = $request->code;

            if (!$shop || !$code) {
                return response()->json(['error' => 'Invalid OAuth response'], 400);
            }

            // Exchange code for access token
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => config('services.shopify.key'),
                'client_secret' => config('services.shopify.secret'),
                'code' => $code,
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to retrieve access token'], 500);
            }

            $accessToken = $response->json()['access_token'];

            // OPTIONAL: Prevent duplicate shop linking
            $existingUser = \App\Models\User::where('shop_domain', $shop)->first();

            if ($existingUser) {
                // If already linked, just update token (reinstall case)
                $existingUser->shopify_access_token = $accessToken;
                $existingUser->save();

                return redirect("https://scs-green-pi.vercel.app/?shop={$shop}");
            }

            // Store temporarily in session for linking step
            session([
                'oauth_shop' => $shop,
                'oauth_access_token' => $accessToken,
            ]);

            // Redirect back to React linking screen
            return redirect("https://scs-green-pi.vercel.app/?shop={$shop}");
        }

}
