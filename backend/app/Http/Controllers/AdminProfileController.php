<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    public function index()
    {
        /** @var User $admin */
        $admin = Auth::user();

        return view('admin.profile.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fullName = trim(sprintf('%s%s %s',
            $validated['first_name'],
            $validated['middle_initial'] ? ' ' . strtoupper(substr($validated['middle_initial'], 0, 1)) . '.' : '',
            $validated['surname']
        ));

        $admin->name = $fullName;
        $admin->first_name = $validated['first_name'];
        $admin->middle_initial = $validated['middle_initial'];
        $admin->surname = $validated['surname'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_image')) {
            if ($admin->profile_image && Storage::disk('public')->exists($admin->profile_image)) {
                Storage::disk('public')->delete($admin->profile_image);
            }

            $path = $request->file('profile_image')->store('profile_images', 'public');
            $admin->profile_image = $path;
        }

        $admin->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }
}