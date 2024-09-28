<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login form submission
    public function login(Request $request)
    {
        // Validate the login input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempt to authenticate the user using the 'web' guard
        if (Auth::guard('web')->attempt($credentials)) {
            // If successful, regenerate the session and redirect to the welcome page
            $request->session()->regenerate();

            // Redirect to the intended page or the welcome page
            return redirect()->intended('/welcome');
        }

        // If authentication fails, return back with an error message
        return back()->with('error', 'Invalid login credentials');
    }


    // Log the user out
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
