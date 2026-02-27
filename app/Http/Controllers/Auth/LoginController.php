<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login'); // or whatever your login blade path is
    }

  public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $user = Auth::user();

        // Admin check
        if ($user->is_admin == 1 && $user->userRole == 1) {
            return redirect('/');
        }

        // Customer check
        if ($user->userRole == 2 && $user->is_admin == 0) {
            return redirect('/');
        }

        Auth::logout();
        return back()->withErrors([
            'email' => 'Unauthorized access.',
        ]);
    }

    return back()->withErrors([
        'email' => 'Invalid email or password.',
    ]);
}
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    //  public function connect(Request $request)
    // {
    //     // ✅ Validate request
    //     $request->validate([
    //         'app_token' => 'required|string'
    //     ]);

    //     // ✅ Check token in users table
    //     $user = User::where('app_token', $request->app_token)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not found'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'User connected successfully',
    //         'data'    => [
    //             'user_id' => $user->id,
    //             'name'    => $user->name,
    //             'email'   => $user->email,
    //         ]
    //     ], 200);
    // }


    public function connect(Request $request)
    {
        $request->validate([
            'app_token' => 'required|string'
        ]);

        // 🔐 Step 1 — Verify Shopify session token
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json([
                'status' => false,
                'message' => 'Missing Shopify session token'
            ], 401);
        }

        $jwt = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode(
                $jwt,
                new Key(config('services.shopify.secret'), 'HS256')
            );

            $shop = str_replace('https://', '', $decoded->dest);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Shopify session token'
            ], 401);
        }

        // 🔑 Step 2 — Find Laravel user by app_token
        $user = User::where('app_token', $request->app_token)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }

        // 🏬 Step 3 — Get OAuth data from session
        $shopRecord = Shop::where('shop_domain', $shop)->first();

        if (!$shopRecord) {
            return response()->json([
                'status' => false,
                'message' => 'OAuth not completed for this store'
            ], 400);
        }

        // 🚫 Prevent store hijacking
        $existing = User::where('shop_domain', $shop)->first();

        if ($existing && $existing->id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'This store is already linked to another account'
            ], 403);
        }

        // 🔗 Step 4 — Link store to user
        $user->shop_domain = $shop;
        $user->shopify_access_token = $accessToken;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'User connected successfully',
            'data'    => [
                'user_id' => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'shop'    => $shop
            ]
        ], 200);
    }

}
