<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = Auth::user();
        $isSuperAdmin = $loggedInUser && $loggedInUser->role === 'superadmin';

        $type = $request->get('type', 'teacher');
        $search = $request->get('search', '');
        $filterBy = $request->get('filter_by', 'all'); // all, first_name, middle_initial, surname

        $allowedTypes = ['teacher', 'student'];

        if ($isSuperAdmin) {
            $allowedTypes[] = 'admin';
        }

        if (!in_array($type, $allowedTypes)) {
    $type = $allowedTypes[0];
}

        $query = User::query()
            ->where('role', $type)
            ->when($search, function ($q) use ($search, $filterBy) {
                $q->where(function ($sub) use ($search, $filterBy) {
                    if ($filterBy === 'first_name') {
                        $sub->where('first_name', 'like', "%{$search}%");
                    } elseif ($filterBy === 'middle_initial') {
                        $sub->where('middle_initial', 'like', "%{$search}%");
                    } elseif ($filterBy === 'surname') {
                        $sub->where('surname', 'like', "%{$search}%");
                    } else {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_initial', 'like', "%{$search}%")
                            ->orWhere('surname', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }
                });
            })
            ->orderByDesc('created_at');

        $users = $query->paginate(10)->withQueryString();

        $stats = [
            'teachers_count'     => User::where('role', 'teacher')->count(),
            'students_count'     => User::where('role', 'student')->count(),
            'admins_count'       => User::where('role', 'admin')->count(),
            'activated_count'    => User::where('status', 'active')->count(),
            'deactivated_count'  => User::where('status', 'deactivated')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.dashboard.partials.users_table', compact('users', 'type', 'isSuperAdmin'))->render(),
            ]);
        }

        return view('admin.dashboard.index', compact(
            'users',
            'stats',
            'type',
            'search',
            'filterBy',
            'isSuperAdmin'
        ));
    }
}