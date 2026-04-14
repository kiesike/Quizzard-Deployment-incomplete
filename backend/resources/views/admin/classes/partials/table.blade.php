<style>
    .class-row-hoverable {
        cursor: pointer;
        transition: background-color 0.18s ease, box-shadow 0.18s ease;
    }

    .class-row-hoverable:hover > td {
        background-color: #dbeafe !important;
    }

    .class-row-hoverable:hover .class-main-cell {
        background-color: #c7d2fe !important;
        border-color: #818cf8 !important;
    }

    .class-row-hoverable:hover .class-main-title {
        color: #312e81 !important;
    }

    .class-row-hoverable:hover .class-main-desc,
    .class-row-hoverable:hover .class-meta-text {
        color: #334155 !important;
    }
</style>

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
            <tr
    class="class-row class-row-hoverable group"
    data-url="{{ route('admin.classes.details', $class->id) }}"
>
    <td class="px-6 py-4">
        <div class="class-main-cell min-w-0 rounded-2xl border border-transparent p-3 transition duration-200">
            <div class="class-main-title font-semibold text-slate-800">
                {{ $class->name }}
            </div>

            <div class="class-main-desc mt-1 max-w-xs truncate text-sm text-slate-500">
                {{ $class->description ?: 'No description' }}
            </div>
        </div>
    </td>

    <td class="class-meta-text px-6 py-4 text-sm text-slate-700">
        {{ $class->teacher->name ?? 'Unknown Teacher' }}
    </td>

    <td class="class-meta-text px-6 py-4 text-sm text-slate-700">
        {{ $class->students->count() }}
    </td>

    <td class="class-meta-text px-6 py-4 text-sm text-slate-700">
        {{ $class->class_code }}
    </td>

    <td class="class-meta-text px-6 py-4 text-sm text-slate-700">
        {{ $class->created_at ? $class->created_at->format('M d, Y') : '—' }}
    </td>

    <td class="px-6 py-4">
        <div class="flex items-center justify-center gap-2 flex-wrap">

            <button
                type="button"
                class="edit-class-btn class-action-btn inline-flex items-center justify-center rounded-xl bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-200 disabled:cursor-not-allowed disabled:opacity-60"
                data-id="{{ $class->id }}"
                data-loading-text="Loading..."
            >
                <span class="flex items-center justify-center gap-2">
                    <span class="spinner hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                    <span>Update</span>
                </span>
            </button>

            <button
                type="button"
                class="delete-class-btn class-action-btn inline-flex items-center justify-center rounded-xl bg-rose-100 px-4 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-200 disabled:cursor-not-allowed disabled:opacity-60"
                data-id="{{ $class->id }}"
                data-name="{{ $class->name }}"
                data-loading-text="Loading..."
            >
                <span class="flex items-center justify-center gap-2">
                    <span class="spinner hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                    <span>Delete</span>
                </span>
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