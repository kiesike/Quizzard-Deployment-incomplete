<div id="deleteClassModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-rose-600">Delete Class</h2>
        </div>

        <form id="deleteClassForm">
            @csrf
            <div class="px-6 py-5">
                <p class="text-sm text-slate-700">
                    Are you sure you want to delete
                    <span id="deleteClassName" class="font-bold text-slate-900"></span>?
                </p>
                <p class="mt-2 text-sm text-rose-500">
                    This action cannot be undone and will also remove related student enrollments and assigned quiz links.
                </p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                <button type="button" class="close-modal rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>