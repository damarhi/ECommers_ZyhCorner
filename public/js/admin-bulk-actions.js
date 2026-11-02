/**
 * admin-bulk-actions.js
 * Universal reusable handler for admin bulk actions (check-all, row-selection, counter, and SweetAlert2 confirmation)
 */
(function() {
    function initBulkActions() {
        const rowSelector = '[data-bulk-item], .row-checkbox, .product-row-checkbox, .category-row-checkbox, .customer-row-checkbox, .cassier-row-checkbox, .order-row-checkbox, .retur-row-checkbox, .banner-row-checkbox, .discount-row-checkbox, .expired-row-checkbox, .message-checkbox';
        const checkAllSelector = '[data-bulk-check-all], #checkAll, #checkAllProducts, #checkAllCategories, #checkAllCustomers, #checkAllCassiers, #checkAllOrders, #checkAllReturs, #checkAllBanners, #checkAllDiscounts, #checkAllExpired, #selectAll, #selectAllCheckbox';

        const rowCheckboxes = document.querySelectorAll(rowSelector);
        const checkAll = document.querySelector(checkAllSelector);
        const btnBulkDelete = document.getElementById('btnBulkDelete') || document.querySelector('[data-bulk-delete-btn]');
        const selectedCountSpan = document.getElementById('selectedCount') || document.querySelector('[data-bulk-count]');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm') || document.querySelector('[data-bulk-form]');
        const inputsContainer = document.getElementById('bulkDeleteInputs') || document.querySelector('[data-bulk-inputs]');
        const bulkDeleteIdsInput = document.getElementById('bulkDeleteIds');

        if (!btnBulkDelete && !bulkDeleteForm && rowCheckboxes.length === 0) {
            return;
        }

        function getSelectedCheckboxes() {
            return Array.from(document.querySelectorAll(rowSelector)).filter(cb => cb.checked);
        }

        function updateBulkDeleteState() {
            const currentRows = document.querySelectorAll(rowSelector);
            const selected = getSelectedCheckboxes();
            const count = selected.length;

            if (selectedCountSpan) {
                selectedCountSpan.textContent = count;
            }

            if (btnBulkDelete) {
                if (count > 0) {
                    btnBulkDelete.classList.remove('d-none');
                    btnBulkDelete.classList.add('d-inline-flex');
                    btnBulkDelete.classList.add('d-flex');
                } else {
                    btnBulkDelete.classList.add('d-none');
                    btnBulkDelete.classList.remove('d-inline-flex');
                    btnBulkDelete.classList.remove('d-flex');
                }
            }

            if (checkAll && currentRows.length > 0) {
                checkAll.checked = count === currentRows.length;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const currentRows = document.querySelectorAll(rowSelector);
                currentRows.forEach(cb => {
                    cb.checked = checkAll.checked;
                });
                updateBulkDeleteState();
            });
        }

        // Delegate change event for row checkboxes to support dynamic updates
        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches(rowSelector)) {
                updateBulkDeleteState();
            }
        });

        // Initialize state
        updateBulkDeleteState();

        function triggerBulkDelete() {
            const selected = getSelectedCheckboxes();
            const count = selected.length;
            if (count === 0) return;

            let entityName = 'Data';
            if (btnBulkDelete && btnBulkDelete.dataset.entity) {
                entityName = btnBulkDelete.dataset.entity;
            } else {
                const pageTitle = document.title || '';
                if (/produk kustom/i.test(pageTitle)) entityName = 'Produk Kustom';
                else if (/produk/i.test(pageTitle)) entityName = 'Produk';
                else if (/kategori/i.test(pageTitle)) entityName = 'Kategori';
                else if (/pelanggan/i.test(pageTitle)) entityName = 'Pelanggan';
                else if (/kasir/i.test(pageTitle)) entityName = 'Akun Kasir';
                else if (/pesanan/i.test(pageTitle)) entityName = 'Pesanan';
                else if (/retur/i.test(pageTitle)) entityName = 'Retur';
                else if (/banner/i.test(pageTitle)) entityName = 'Banner';
                else if (/diskon/i.test(pageTitle)) entityName = 'Diskon';
                else if (/kadaluarsa|expired/i.test(pageTitle)) entityName = 'Produk Kadaluarsa';
                else if (/pesan/i.test(pageTitle)) entityName = 'Pesan';
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `Hapus ${entityName} Terpilih?`,
                    text: `Apakah Anda yakin ingin menghapus ${count} ${entityName.toLowerCase()} yang dipilih? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus Terpilih!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4',
                        confirmButton: 'btn btn-gradient-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed && bulkDeleteForm) {
                        if (inputsContainer) {
                            inputsContainer.innerHTML = '';
                            selected.forEach(cb => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'ids[]';
                                input.value = cb.value;
                                inputsContainer.appendChild(input);
                            });
                        }
                        if (bulkDeleteIdsInput) {
                            bulkDeleteIdsInput.value = selected.map(cb => cb.value).join(',');
                        }
                        bulkDeleteForm.submit();
                    }
                });
            } else {
                if (confirm(`Apakah Anda yakin ingin menghapus ${count} ${entityName.toLowerCase()} terpilih?`)) {
                    if (inputsContainer) {
                        inputsContainer.innerHTML = '';
                        selected.forEach(cb => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = cb.value;
                            inputsContainer.appendChild(input);
                        });
                    }
                    if (bulkDeleteIdsInput) {
                        bulkDeleteIdsInput.value = selected.map(cb => cb.value).join(',');
                    }
                    bulkDeleteForm.submit();
                }
            }
        }

        if (btnBulkDelete && bulkDeleteForm) {
            btnBulkDelete.addEventListener('click', function(e) {
                e.preventDefault();
                triggerBulkDelete();
            });
        }

        // Global functions for inline onclick handlers if present
        window.confirmBulkDelete = triggerBulkDelete;
        window.updateBulkDeleteState = updateBulkDeleteState;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBulkActions);
    } else {
        initBulkActions();
    }
})();
