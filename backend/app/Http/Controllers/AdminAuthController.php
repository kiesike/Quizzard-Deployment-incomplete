<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'superadmin'])) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Only admin or superadmin accounts can access the admin panel.',
            ])->onlyInput('email');
        }

        if ($user->status !== 'active') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account is not active.',
            ])->onlyInput('email');
        }

        $welcomeMessage = $user->role === 'superadmin'
            ? 'Welcome back, Super Admin!'
            : 'Welcome back, Admin!';

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $welcomeMessage);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}