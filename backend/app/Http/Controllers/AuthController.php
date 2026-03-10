<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Maximum failed attempts before lockout
    const MAX_ATTEMPTS = 5;
    // Lockout duration in minutes
    const LOCKOUT_MINUTES = 15;

    public function login(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Check if account is deactivated
        if ($user->status === 'deactivated') {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact the administrator.'
            ], 403);
        }

        // Check if account is pending approval
        if ($user->status === 'pending') {
            return response()->json([
                'message' => 'Your account is pending approval. Please wait for an administrator to activate your account.'
            ], 403);
        }

        // Check if account is locked
        if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            $minutesLeft = Carbon::now()->diffInMinutes($user->locked_until, false);
            return response()->json([
                'message' => "Your account is locked. Please try again in {$minutesLeft} minute(s)."
            ], 423);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            // Increment failed attempts
            $user->failed_login_attempts += 1;

            // Lock account if max attempts reached
            if ($user->failed_login_attempts >= self::MAX_ATTEMPTS) {
                $user->locked_until = Carbon::now()->addMinutes(self::LOCKOUT_MINUTES);
                $user->save();
                return response()->json([
                    'message' => 'Too many failed attempts. Your account has been locked for 15 minutes.'
                ], 423);
            }

            $user->save();
            $remaining = self::MAX_ATTEMPTS - $user->failed_login_attempts;
            return response()->json([
                'message' => "Invalid credentials. {$remaining} attempt(s) remaining before lockout."
            ], 401);
        }

        // Successful login — reset failed attempts
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        // Create and return token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        // Delete the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.'
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}