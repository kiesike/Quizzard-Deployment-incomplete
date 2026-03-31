import './bootstrap';

// Activation / Deactivation AJAX logic
document.addEventListener('DOMContentLoaded', function () {
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
            const originalText = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.innerHTML = 'Processing...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
            } catch (error) {
                console.error(error);

                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                }

                allButtons.forEach((btn) => {
                    btn.disabled = false;
                });

                alert('Failed to update account status.');
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

        viewPassword.addEventListener('click', function () {
            if (viewPassword.textContent) {
                navigator.clipboard.writeText(viewPassword.textContent);
            }
        });
    }
});