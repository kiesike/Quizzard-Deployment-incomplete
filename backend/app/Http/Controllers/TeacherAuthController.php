<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('teacher.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== 'teacher') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Only teacher accounts can access the teacher panel.',
            ])->onlyInput('email');
        }

        if ($user->status !== 'active') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your teacher account is not active.',
            ])->onlyInput('email');
        }

        return redirect()->route('teacher.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.login');
    }
}