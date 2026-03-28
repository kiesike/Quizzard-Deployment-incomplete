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
        $filterBy = $request->get('filter_by', 'all'); // all, first_name, middle_initial, surname

        $query = User::query()
            ->whereIn('role', ['teacher', 'student']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $filterBy) {
                if ($filterBy === 'first_name') {
                    $q->where('first_name', 'like', "%{$search}%");
                } elseif ($filterBy === 'middle_initial') {
                    $q->where('middle_initial', 'like', "%{$search}%");
                } elseif ($filterBy === 'surname') {
                    $q->where('surname', 'like', "%{$search}%");
                } else {
                    // Search all name fields + email + role
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_initial', 'like', "%{$search}%")
                      ->orWhere('surname', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
                }
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

        return view('admin.activation.index', compact('users', 'search', 'status', 'stats', 'filterBy'));
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