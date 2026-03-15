<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.dashboard', $request->only('type', 'search'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'teacher');

        if (!in_array($type, ['teacher', 'student'])) {
            $type = 'teacher';
        }

        return view('admin.users.create', compact('type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['teacher', 'student'])],
            'status' => ['required', Rule::in(['pending', 'active', 'deactivated'])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.dashboard', ['type' => $validated['role']])
            ->with('success', 'Account created successfully.');
    }

    public function show(User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        if (request()->ajax()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => optional($user->created_at)->format('F d, Y h:i A'),
                'updated_at' => optional($user->updated_at)->format('F d, Y h:i A'),
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['teacher', 'student'])],
            'status' => ['required', Rule::in(['pending', 'active', 'deactivated'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.dashboard', ['type' => $validated['role']])
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        $role = $user->role;
        $user->delete();

        return redirect()
            ->route('admin.dashboard', ['type' => $role])
            ->with('success', 'Account deleted successfully.');
    }
}