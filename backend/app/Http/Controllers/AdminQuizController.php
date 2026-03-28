<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminQuizController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $sort = $request->sort ?? 'latest';
        $filterBy = $request->get('filter_by', 'all'); // all, first_name, middle_initial, surname

        $query = ClassRoom::with(['teacher', 'students']);

        if ($search) {
    $query->where(function ($q) use ($search, $filterBy) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhereHas('teacher', function ($teacherQuery) use ($search, $filterBy) {
              if ($filterBy === 'first_name') {
                  $teacherQuery->where('first_name', 'like', "%{$search}%");
              } elseif ($filterBy === 'middle_initial') {
                  $teacherQuery->where('middle_initial', 'like', "%{$search}%");
              } elseif ($filterBy === 'surname') {
                  $teacherQuery->where('surname', 'like', "%{$search}%");
              } else {
                  $teacherQuery->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_initial', 'like', "%{$search}%")
                      ->orWhere('surname', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
              }
          });
    });
}

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $classes = $query->paginate(10);

        $activeTeachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->count();

        $studentsCount = User::where('role', 'student')->count();

        $classesCount = ClassRoom::count();
        $totalEnrollments = DB::table('class_students')->count();

        if ($request->ajax()) {
            return view('admin.classes.partials.table', compact('classes'))->render();
        }

        return view('admin.classes.index', compact(
            'classes',
            'activeTeachers',
            'studentsCount',
            'classesCount',
            'totalEnrollments'
        ));
    }

    public function update(Request $request, $id)
    {
        $class = ClassRoom::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $class->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $class = ClassRoom::findOrFail($id);
        $class->delete();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $class = ClassRoom::with(['teacher', 'students'])->findOrFail($id);
        return response()->json($class);
    }
}