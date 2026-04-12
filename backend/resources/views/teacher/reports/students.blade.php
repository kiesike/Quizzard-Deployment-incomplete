@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="rounded-3xl bg-green-700 p-6 text-white sm:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-widest text-green-300">Teacher Reports</p>
                    <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">Students</h2>
                    <p class="mt-2 max-w-2xl text-sm text-green-100">
                        Review student participation and performance across your classes and quizzes.
                    </p>
                </div>
                
            </div>
        </div>

        {{-- Table Card --}}
        <div class="rounded-3xl bg-white ring-1 ring-slate-200">
        
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Student performance report</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Only students enrolled in your classes are shown.</p>
                </div>
                <a href="{{ route('teacher.reports.students.export') }}"
                    class="items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 mr-6 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                    ↓ Export to Excel
                </a>
            </div>

            @if ($students->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-400">No students found.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">First Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Last Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Gender</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Date of Birth</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Grade Level</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($students as $student)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $student->first_name }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $student->surname }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->student_id ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->gender ? ucfirst($student->studentProfile->gender) : '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->contact_number ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->grade_level ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->section ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            onclick="openClassModal({{ $student->id }}, {{ json_encode($student->enrolled_classes) }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                            View Quiz Info
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Class Picker Modal --}}
    <div id="classModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-slate-800">Select a Class</h3>
            <p class="mt-1 text-sm text-slate-500">Choose which class to view quiz info for this student.</p>
            <div id="classModalList" class="mt-4 space-y-2 hover:bg-slate-50"></div>
            <button onclick="closeClassModal()"
                class="mt-4 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Cancel
            </button>
        </div>
    </div>

    <script>
        function openClassModal(studentId, classes) {
            const list = document.getElementById('classModalList');
            list.innerHTML = '';

            if (!classes.length) {
                list.innerHTML = '<p class="text-xs text-slate-400">No classes found.</p>';
            } else {
                classes.forEach(cls => {
                    const btn = document.createElement('button');
                    btn.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-green-50 hover:border-green-300 hover:text-green-800 transition-colors';
                    btn.textContent = cls.name;
                    btn.onclick = () => window.location.href = `/teacher/reports/students/${studentId}/classes/${cls.id}`;
                    list.appendChild(btn);
                });
            }

            document.getElementById('classModal').classList.replace('hidden', 'flex');
        }

        function closeClassModal() {
            document.getElementById('classModal').classList.replace('flex', 'hidden');
        }
    </script>
@endsection