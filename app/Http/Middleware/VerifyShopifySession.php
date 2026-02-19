<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class VerifyShopifySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode(
                $token,
                new Key(config('services.shopify.secret'), 'HS256')
            );

            $shop = str_replace('https://', '', $decoded->dest);

            $user = User::where('shop_domain', $shop)->first();

            if (!$user) {
                return response()->json(['error' => 'Shop not connected'], 404);
            }

            $request->attributes->add(['auth_user' => $user]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
