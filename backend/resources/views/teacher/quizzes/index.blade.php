@extends('teacher.layouts.app')

@section('title', 'My Quizzes')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Quizzes</h1>
            <p class="text-sm text-slate-500 mt-1">Create and manage your quizzes</p>
        </div>
        <a href="{{ route('teacher.quizzes.create') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow transition">
            + New Quiz
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow p-6">
        @if($quizzes->isEmpty())
            <p class="text-slate-500 text-sm text-center py-8">No quizzes yet. Click <strong>+ New Quiz</strong> to get started.</p>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Questions</th>
                            <th class="px-4 py-3 text-left">Attempts</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Created</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quizzes as $quiz)
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $quiz->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $quiz->questions_count }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $quiz->attempts_count }}</td>
                            <td class="px-4 py-3">
                                @if($quiz->is_published)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Published</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $quiz->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('teacher.quizzes.manage', $quiz->id) }}"
                                   class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
