<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
{
    $type = $request->get('type', 'teacher');
    $search = $request->get('search', '');

    if (!in_array($type, ['teacher', 'student'])) {
        $type = 'teacher';
    }

    $query = User::query()
        ->where('role', $type)
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('created_at');

    $users = $query->paginate(10)->withQueryString();

    $stats = [
        'teachers_count'     => User::where('role', 'teacher')->count(),
        'students_count'     => User::where('role', 'student')->count(),
        'activated_count'    => User::where('status', 'active')->count(),
        'deactivated_count'  => User::where('status', 'deactivated')->count(),
    ];

    if ($request->ajax()) {
        return response()->json([
            'html' => view('admin.dashboard.partials.users_table', compact('users', 'type'))->render(),
        ]);
    }

    return view('admin.dashboard.index', compact('users', 'stats', 'type', 'search'));
}
}