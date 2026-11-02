// Helper: Update Badge Posisi Edit
        function updatePosisiBadge(selectEl, id) {
            const badge = document.getElementById('badgePosisi' + id);
            if (!badge) return;
            const labels = {
                'home_hero': 'Home Hero',
                'shop_header': 'Header Showcase',
                'custom_header': 'Header Kustom',
                'popup_promo': 'Pop-up Promo'
            };
            badge.textContent = labels[selectEl.value] || 'Banner';
        }

        // Helper: Update Badge Posisi Add
        function updatePosisiBadgeAdd(selectEl) {
            const badge = document.getElementById('badgePosisiAdd');
            if (!badge) return;
            const labels = {
                'home_hero': 'Home Hero',
                'shop_header': 'Header Showcase',
                'custom_header': 'Header Kustom',
                'popup_promo': 'Pop-up Promo'
            };
            badge.textContent = labels[selectEl.value] || 'Banner';
        }

        // Live Preview: Edit Banner
        function previewEditBanner(event, id) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('editPreviewImg' + id);
                const btnReset = document.getElementById('btnResetImg' + id);
                if (img) img.src = e.target.result;
                if (btnReset) btnReset.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        // Reset Preview: Edit Banner
        function resetEditBanner(id) {
            const input = document.getElementById('inputGambarEdit' + id);
            const img = document.getElementById('editPreviewImg' + id);
            const btnReset = document.getElementById('btnResetImg' + id);
            if (input) input.value = '';
            if (img && img.dataset.originalSrc) img.src = img.dataset.originalSrc;
            if (btnReset) btnReset.classList.add('d-none');
        }

        // Live Preview: Add Banner
        function previewAddBanner(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('addPreviewImg');
                const placeholder = document.getElementById('addPreviewPlaceholder');
                const btnReset = document.getElementById('btnResetAddImg');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                }
                if (placeholder) placeholder.classList.add('d-none');
                if (btnReset) btnReset.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        // Reset Preview: Add Banner
        function resetAddBanner() {
            const input = document.getElementById('inputGambarAdd');
            const img = document.getElementById('addPreviewImg');
            const placeholder = document.getElementById('addPreviewPlaceholder');
            const btnReset = document.getElementById('btnResetAddImg');
            if (input) input.value = '';
            if (img) {
                img.src = '';
                img.classList.add('d-none');
            }
            if (placeholder) placeholder.classList.remove('d-none');
            if (btnReset) btnReset.classList.add('d-none');
        }


