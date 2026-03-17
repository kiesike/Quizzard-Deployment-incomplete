<table class="min-w-full divide-y divide-slate-200">
    <thead class="bg-slate-50">
        <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Class</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teacher</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Students</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Code</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Created</th>
            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($classes as $class)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-4">
                    <div class="font-semibold text-slate-800">{{ $class->name }}</div>
                    <div class="text-sm text-slate-500 max-w-xs truncate">
                        {{ $class->description ?: 'No description' }}
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ $class->teacher->name ?? 'Unknown Teacher' }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ $class->students->count() }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ $class->class_code }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700">
                    {{ $class->created_at ? $class->created_at->format('M d, Y') : '—' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                        <button
                            class="view-class-btn rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-200 transition"
                            data-id="{{ $class->id }}"
                        >
                            View
                        </button>

                        <button
                            class="edit-class-btn rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200 transition"
                            data-id="{{ $class->id }}"
                        >
                            Update
                        </button>

                        <button
                            class="delete-class-btn rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition"
                            data-id="{{ $class->id }}"
                            data-name="{{ $class->name }}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                    No classes found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($classes->hasPages())
    <div class="border-t border-slate-200 bg-white px-6 py-4">
        {{ $classes->links() }}
    </div>
@endif