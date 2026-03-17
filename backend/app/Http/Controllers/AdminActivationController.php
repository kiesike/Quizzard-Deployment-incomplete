<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminActivationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', 'all');

        $query = User::query()
            ->whereIn('role', ['teacher', 'student']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::whereIn('role', ['teacher', 'student'])->count(),
            'pending' => User::whereIn('role', ['teacher', 'student'])->where('status', 'pending')->count(),
            'active' => User::whereIn('role', ['teacher', 'student'])->where('status', 'active')->count(),
            'deactivated' => User::whereIn('role', ['teacher', 'student'])->where('status', 'deactivated')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.activation.partials.users_table', compact('users'))->render(),
            ]);
        }

        return view('admin.activation.index', compact('users', 'search', 'status', 'stats'));
    }

    public function activate(User $user)
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