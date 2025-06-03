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
            return redirect()->route('home', [app()->getLocale()]);
        }

        // If authentication fails, return back with an error message
        return back()->with('error', 'Invalid login credentials');
    }


    // Log the user out
    public function logout(Request $request)
    {
        // Log the user out
        Auth::logout();

        // Invalidate the current session
        $request->session()->invalidate();

        // Regenerate the CSRF token for security
        $request->session()->regenerateToken();

        // Redirect the user to the login page in the current locale
        return redirect()->route('login', [app()->getLocale()]);
    }

}
