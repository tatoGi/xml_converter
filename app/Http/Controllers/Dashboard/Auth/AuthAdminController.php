<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthAdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // Handle login form submission
    public function store(Request $request)
    {
        // Add debugging
        \Log::info('Login attempt details', [
            'has_csrf_token' => $request->has('_token'),
            'csrf_token' => $request->input('_token'),
            'debug_token' => $request->input('debug_token'),
            'session_token' => session()->token(),
            'headers' => $request->headers->all(),
            'all_input' => $request->all()
        ]);

        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        // Add debugging
        Log::info('Login attempt', [
            'email' => $credentials['email'],
            'admin_exists' => \App\Models\Admin::where('email', $credentials['email'])->exists()
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            Log::info('Login successful');
            return redirect()->route('admin', [app()->getLocale()]);
        }

        Log::info('Login failed');
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Log the user out
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login', [app()->getLocale()]);
    }
}
