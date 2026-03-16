<div id="viewClassModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-800">View Class</h2>
            <button class="close-modal text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="space-y-4 px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class Title</p>
                <p id="viewClassName" class="mt-1 text-base text-slate-800">—</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class Code</p>
                    <p id="viewClassCode" class="mt-1 text-base text-slate-800">—</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teacher</p>
                    <p id="viewClassTeacher" class="mt-1 text-base text-slate-800">—</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</p>
                <p id="viewClassDescription" class="mt-1 text-base text-slate-800 whitespace-pre-line">—</p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Students Enrolled</p>
                <p id="viewClassStudentCount" class="mt-1 text-base text-slate-800">0</p>
            </div>
        </div>

        <div class="flex justify-end border-t border-slate-200 px-6 py-4">
            <button class="close-modal rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Close
            </button>
        </div>
    </div>
</div>