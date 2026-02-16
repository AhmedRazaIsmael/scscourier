<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            return redirect('/'); // Or wherever you want to land
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

     public function connect(Request $request)
    {
        // ✅ Validate request
        $request->validate([
            'app_token' => 'required|string'
        ]);

        // ✅ Check token in users table
        $user = User::where('app_token', $request->app_token)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'User connected successfully',
            'data'    => [
                'user_id' => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ]
        ], 200);
    }
}
