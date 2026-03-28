<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('teacher.dashboard.index');
    }

    public function classes(Request $request)
    {
        return view('teacher.reports.classes');
    }

    public function quizzes(Request $request)
    {
        return view('teacher.reports.quizzes');
    }

    public function students(Request $request)
    {
        return view('teacher.reports.students');
    }
}