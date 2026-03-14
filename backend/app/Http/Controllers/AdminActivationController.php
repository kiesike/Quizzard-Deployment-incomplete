<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminActivationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $pendingUsers = User::query()
            ->whereIn('role', ['teacher', 'student'])
            ->where('status', 'pending')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.activation.index', compact('pendingUsers', 'search'));
    }

    public function approve(User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        $user->status = 'active';
        $user->save();

        return back()->with('success', 'Account activated successfully.');
    }

    public function deactivate(User $user)
    {
        abort_if(!in_array($user->role, ['teacher', 'student']), 404);

        $user->status = 'deactivated';
        $user->save();

        return back()->with('success', 'Account deactivated successfully.');
    }
}