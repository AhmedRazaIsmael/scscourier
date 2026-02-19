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

        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('services.shopify.key'),
            'client_secret' => config('services.shopify.secret'),
            'code' => $code,
        ]);

        $accessToken = $response['access_token'];

        // User must already be logged in (since they created account first)
        $user = Auth::user();

        // Prevent store hijacking
        $existing = \App\Models\User::where('shop_domain', $shop)->first();

        if ($existing && $existing->id !== $user->id) {
            abort(403, 'Store already connected to another account.');
        }

        $user->shop_domain = $shop;
        $user->shopify_access_token = $accessToken;
        $user->save();

        return redirect('/app'); // React embedded app route
    }
}
