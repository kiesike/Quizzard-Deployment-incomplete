import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function setButtonLoading(button, isLoading, loadingText = 'Processing...') {
        if (!button) return;

        if (isLoading) {
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }

            button.disabled = true;
            button.innerHTML = `
                <span class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>${loadingText}</span>
                </span>
            `;
            return;
        }

        button.disabled = false;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
        }
    }

    function showToast(message, type = 'success') {
        const oldToast = document.getElementById('globalJsToast');
        if (oldToast) {
            oldToast.remove();
        }

        const toast = document.createElement('div');
        toast.id = 'globalJsToast';

        const toastStyles = {
            success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            error: 'border-red-200 bg-red-50 text-red-800',
            info: 'border-blue-200 bg-blue-50 text-blue-800',
        };

        toast.className = `fixed right-4 top-4 z-[9999] w-full max-w-sm rounded-2xl border px-4 py-4 shadow-lg ${toastStyles[type] || toastStyles.info}`;
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm font-semibold">${type === 'error' ? 'Action Failed' : 'Success'}</p>
                    <p class="mt-1 text-sm">${message}</p>
                </div>
                <button type="button" class="text-lg leading-none opacity-70 hover:opacity-100" aria-label="Close notification">
                    ×
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        const closeBtn = toast.querySelector('button');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => toast.remove());
        }

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    window.showToast = showToast;

    // Activation / Deactivation AJAX logic
    const activationTable = document.getElementById('activationTableContainer');

    if (activationTable) {
        activationTable.addEventListener('submit', async function (e) {
            const form = e.target.closest('form');
            if (!form) return;

            e.preventDefault();

            const allButtons = activationTable.querySelectorAll('.activation-btn');
            allButtons.forEach((btn) => {
                btn.disabled = true;
            });

            const submitBtn = form.querySelector('.activation-btn');
            setButtonLoading(submitBtn, true);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to update status.');
                }

                const tableResponse = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!tableResponse.ok) {
                    throw new Error('Failed to reload activation table.');
                }

                const data = await tableResponse.json();
                activationTable.innerHTML = data.html;

                showToast('Account status updated successfully.', 'success');
            } catch (error) {
                console.error(error);

                setButtonLoading(submitBtn, false);

                allButtons.forEach((btn) => {
                    btn.disabled = false;
                });

                showToast('Failed to update account status.', 'error');
            }
        });
    }

    // Close modal when clicking outside
    document.querySelectorAll('[id$="Modal"]').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // Close modal buttons
    document.querySelectorAll('.close-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = btn.closest('[id$="Modal"]');
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // View modal skeleton loader
    window.showViewModalSkeleton = function (show = true) {
        const skeleton = document.getElementById('viewModalSkeleton');
        if (skeleton) {
            skeleton.classList.toggle('hidden', !show);
        }
    };

    // Edit modal spinner
    window.showEditModalSpinner = function (show = true) {
        const spinner = document.getElementById('editModalSpinner');
        if (spinner) {
            spinner.classList.toggle('hidden', !show);
        }
    };

    // Copy password on click
    const viewPassword = document.getElementById('viewPassword');
    if (viewPassword) {
        viewPassword.style.userSelect = 'all';
        viewPassword.title = 'Click to copy';

        viewPassword.addEventListener('click', async function () {
            if (!viewPassword.textContent) return;

            try {
                await navigator.clipboard.writeText(viewPassword.textContent);
                showToast('Password copied to clipboard.', 'info');
            } catch (error) {
                console.error(error);
                showToast('Failed to copy password.', 'error');
            }
        });
    }
});