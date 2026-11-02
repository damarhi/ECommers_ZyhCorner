/**
 * admin-confirmations.js
 * Universal confirmation dialogs for single deletions, edits, and status changes in Admin panel.
 */
(function() {
    function initConfirmations() {
        // Event delegation for .delete-form submissions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !form.matches('.delete-form, [data-confirm-delete]')) {
                return;
            }

            // If already confirmed by SweetAlert2, let it submit
            if (form.dataset.confirmed === 'true') {
                return;
            }

            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4',
                        confirmButton: 'btn btn-gradient-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin? Data yang dihapus tidak dapat dikembalikan!')) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
        });

        // Event delegation for .edit-form submissions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !form.matches('.edit-form, [data-confirm-edit]')) {
                return;
            }

            if (form.dataset.confirmed === 'true') {
                return;
            }

            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Simpan Perubahan?',
                    text: 'Apakah Anda yakin ingin memperbarui data ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#00B207',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4',
                        confirmButton: 'btn btn-gradient-primary',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm('Simpan Perubahan? Apakah Anda yakin ingin memperbarui data ini?')) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
        });

        // Global helper for specific id-based delete forms (e.g. pesan kontak)
        window.confirmDelete = function(id) {
            const form = document.getElementById('deleteForm-' + id);
            if (!form) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Pesan Ini?',
                    text: 'Pesan kontak ini akan dihapus secara permanen dari database.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4',
                        confirmButton: 'btn btn-gradient-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            } else {
                if (confirm('Hapus pesan kontak ini secara permanen?')) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConfirmations);
    } else {
        initConfirmations();
    }
})();
