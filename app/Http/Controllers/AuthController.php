<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the login page
     */
    public function showLogin()
    {
        // If already logged in, redirect to home
        if (Session::has('user')) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle login submission (demo - no real authentication)
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3',
        ]);

        // Demo login - store username in session
        Session::put('user', [
            'username' => $request->username,
            'name' => ucfirst($request->username),
            'logged_in_at' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Welcome back, ' . ucfirst($request->username) . '!');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Session::forget('user');
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
