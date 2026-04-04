<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminActivationController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = Auth::user();
        $isSuperAdmin = $loggedInUser && $loggedInUser->role === 'superadmin';

        $search = trim($request->get('search', ''));
        $status = $request->get('status', 'all');
        $filterBy = $request->get('filter_by', 'all');
        $roleType = $request->get('role_type', 'all');

        $allowedRoles = ['teacher', 'student'];
        if ($isSuperAdmin) {
            $allowedRoles[] = 'admin';
        }

        if (!in_array($roleType, array_merge(['all'], $allowedRoles))) {
            $roleType = 'all';
        }

        $query = User::query()
            ->whereIn('role', $allowedRoles);

        if ($roleType !== 'all') {
            $query->where('role', $roleType);
        }

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

        $statsBaseQuery = User::whereIn('role', $allowedRoles);

        if ($roleType !== 'all') {
            $statsBaseQuery->where('role', $roleType);
        }

        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'pending' => (clone $statsBaseQuery)->where('status', 'pending')->count(),
            'active' => (clone $statsBaseQuery)->where('status', 'active')->count(),
            'deactivated' => (clone $statsBaseQuery)->where('status', 'deactivated')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.activation.partials.users_table', compact('users', 'isSuperAdmin'))->render(),
            ]);
        }

        return view('admin.activation.index', compact(
            'users',
            'search',
            'status',
            'stats',
            'filterBy',
            'isSuperAdmin',
            'roleType'
        ));
    }

    public function activate(User $user)
    {
        $loggedInUser = Auth::user();
        $isSuperAdmin = $loggedInUser && $loggedInUser->role === 'superadmin';

        $allowedRoles = ['teacher', 'student'];
        if ($isSuperAdmin) {
            $allowedRoles[] = 'admin';
        }

        abort_if(!in_array($user->role, $allowedRoles), 404);

        $user->status = 'active';
        $user->save();

        return back()->with('success', ucfirst($user->role) . ' account activated successfully.');
    }

    public function deactivate(User $user)
    {
        $loggedInUser = Auth::user();
        $isSuperAdmin = $loggedInUser && $loggedInUser->role === 'superadmin';

        $allowedRoles = ['teacher', 'student'];
        if ($isSuperAdmin) {
            $allowedRoles[] = 'admin';
        }

        abort_if(!in_array($user->role, $allowedRoles), 404);

        $user->status = 'deactivated';
        $user->save();

        return back()->with('success', ucfirst($user->role) . ' account deactivated successfully.');
    }
}