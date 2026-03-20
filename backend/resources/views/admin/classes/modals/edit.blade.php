<div id="editClassModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">Update Class</h2>
            <button class="close-modal text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        <form id="editClassForm">
            @csrf
            <input type="hidden" id="editClassId" name="id">

            <div class="space-y-4 px-6 py-5">
                <div>
                    <label for="editClassName" class="block text-sm font-semibold text-slate-700 mb-1">Class Title</label>
                    <input
                        type="text"
                        id="editClassName"
                        name="name"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                </div>

                <div>
                    <label for="editClassDescription" class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                    <textarea
                        id="editClassDescription"
                        name="description"
                        rows="4"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                <button type="button" class="close-modal rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>