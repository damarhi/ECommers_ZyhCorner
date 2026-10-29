/**
 * Live Instant Search Utility (Context & Role Aware)
 * Zyh Corner Cake Shop
 *
 * Mengaktifkan pencarian instan (as-you-type) tanpa reload / enter,
 * dilengkapi tombol clear (x), keyboard shortcut (Escape), dan empty state dinamis.
 */
(function() {
    'use strict';

    function initInstantSearch() {
        // Cari semua search input di halaman
        var searchInputs = document.querySelectorAll('input[name="search"], input[data-instant-search="true"], .instant-search-input');
        if (!searchInputs || searchInputs.length === 0) return;

        searchInputs.forEach(function(input) {
            if (input.dataset.instantSearchInitialized) return;
            input.dataset.instantSearchInitialized = 'true';

            var wrapper = input.parentElement;
            if (wrapper && getComputedStyle(wrapper).position === 'static') {
                wrapper.style.position = 'relative';
            }

            // Tambahkan tombol Clear (×) jika belum ada
            var clearBtn = wrapper.querySelector('.instant-search-clear-btn');
            if (!clearBtn) {
                clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'instant-search-clear-btn';
                clearBtn.setAttribute('aria-label', 'Bersihkan pencarian');
                clearBtn.innerHTML = '&times;';
                clearBtn.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-size: 18px; line-height: 1; color: #94a3b8; cursor: pointer; padding: 2px 6px; border-radius: 50%; display: none; z-index: 4; transition: color 0.15s ease;';
                
                clearBtn.addEventListener('mouseenter', function() { clearBtn.style.color = '#ef4444'; });
                clearBtn.addEventListener('mouseleave', function() { clearBtn.style.color = '#94a3b8'; });
                
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    input.value = '';
                    clearBtn.style.display = 'none';
                    triggerFilter(input, '');
                    input.focus();
                });

                wrapper.appendChild(clearBtn);
            }

            function updateClearBtn() {
                if (input.value.trim().length > 0) {
                    clearBtn.style.display = 'block';
                } else {
                    clearBtn.style.display = 'none';
                }
            }

            // Cek kondisi awal saat halaman dimuat
            updateClearBtn();

            // Cegah reload form saat pengguna menekan tombol Enter jika konten lokal dapat disaring
            var form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (hasLocalFilterableContent()) {
                        e.preventDefault();
                        triggerFilter(input, input.value.trim());
                    }
                });
            }

            // Debounce listener saat pengguna mengetik
            var debounceTimer;
            input.addEventListener('input', function() {
                updateClearBtn();
                clearTimeout(debounceTimer);
                var q = this.value.trim();
                debounceTimer = setTimeout(function() {
                    triggerFilter(input, q);
                }, 150);
            });

            // Keyboard shortcut: Escape membersihkan pencarian
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (this.value.length > 0) {
                        this.value = '';
                        updateClearBtn();
                        triggerFilter(input, '');
                    }
                    this.blur();
                }
            });
        });
    }

    function hasLocalFilterableContent() {
        return document.querySelector('#product-grid, .pos-product-item, .shop-product-col, .banner-col-item, .table tbody tr') !== null;
    }

    function triggerFilter(input, query) {
        var q = query.toLowerCase();

        // 1. Filter Grid POS Kasir
        var posItems = document.querySelectorAll('#product-grid .pos-product-item, #product-grid .col-6');
        if (posItems && posItems.length > 0) {
            filterGridItems(posItems, q, '#product-grid', 'Tidak ada produk kasir yang cocok dengan kata kunci');
            syncUrlParam('search', query);
            return;
        }

        // 2. Filter Grid Banner Admin
        var bannerItems = document.querySelectorAll('.banner-col-item');
        if (bannerItems && bannerItems.length > 0) {
            var bannerContainer = bannerItems[0].parentElement;
            filterGridItems(bannerItems, q, bannerContainer, 'Tidak ada banner yang cocok dengan kata kunci');
            syncUrlParam('search', query);
            return;
        }

        // 3. Filter Grid Katalog Toko Pelanggan (/shop & /custom-products)
        var shopItems = document.querySelectorAll('.shop-product-col, .product-grid .product-card');
        if (shopItems && shopItems.length > 0) {
            var shopContainer = document.querySelector('.product-grid');
            filterGridItems(shopItems, q, shopContainer, 'Tidak ada produk yang cocok dengan kata kunci');
            syncUrlParam('search', query);
            return;
        }

        // 4. Filter Baris Tabel (Admin Orders, All Sales, Messages, Products, Customers, Discounts, etc.)
        var table = document.querySelector('.table tbody');
        if (table) {
            var rows = table.querySelectorAll('tr:not(.empty-placeholder-row):not(.instant-search-no-results)');
            if (rows && rows.length > 0) {
                filterTableRows(table, rows, q);
                syncUrlParam('search', query);
                return;
            }
        }
    }

    function filterGridItems(items, q, containerTarget, emptyMsg) {
        var visibleCount = 0;
        var container = typeof containerTarget === 'string' ? document.querySelector(containerTarget) : containerTarget;

        items.forEach(function(item) {
            var searchData = (item.getAttribute('data-search') || item.innerText || '').toLowerCase();
            var isMatch = (q === '') || (searchData.indexOf(q) !== -1);
            item.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleCount++;
        });

        if (container) {
            var noResultsEl = container.querySelector('.instant-search-no-results-grid');
            if (visibleCount === 0) {
                if (!noResultsEl) {
                    noResultsEl = document.createElement('div');
                    noResultsEl.className = 'col-12 text-center py-5 instant-search-no-results-grid';
                    noResultsEl.style.cssText = 'grid-column: 1 / -1; width: 100%; text-align: center; padding: 48px 16px;';
                    noResultsEl.innerHTML = 
                        '<div class="py-4">' +
                            '<i class="mdi mdi-magnify-close text-muted d-block mb-3" style="font-size: 48px; opacity: 0.6;"></i>' +
                            '<h5 class="text-dark font-weight-bold mb-1" style="font-size: 16px;">' + emptyMsg + ' "' + escapeHtml(q) + '"</h5>' +
                            '<p class="text-muted" style="font-size: 13.5px;">Coba gunakan kata kunci lain atau periksa ejaan Anda.</p>' +
                        '</div>';
                    container.appendChild(noResultsEl);
                } else {
                    noResultsEl.style.display = 'block';
                    var titleEl = noResultsEl.querySelector('h5');
                    if (titleEl) titleEl.innerText = emptyMsg + ' "' + q + '"';
                }
            } else if (noResultsEl) {
                noResultsEl.style.display = 'none';
            }
        }
    }

    function filterTableRows(table, rows, q) {
        var visibleCount = 0;
        var colCount = 6;
        var theadRow = table.closest('table').querySelector('thead tr');
        if (theadRow) {
            colCount = theadRow.children.length;
        }

        rows.forEach(function(row) {
            var searchData = (row.getAttribute('data-search') || row.innerText || '').toLowerCase();
            var isMatch = (q === '') || (searchData.indexOf(q) !== -1);
            row.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleCount++;
        });

        var noResultsRow = table.querySelector('.instant-search-no-results');
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'instant-search-no-results';
                noResultsRow.innerHTML = 
                    '<td colspan="' + colCount + '" class="text-center py-5 text-muted">' +
                        '<i class="mdi mdi-file-search-outline d-block mb-2" style="font-size: 36px; opacity: 0.5;"></i>' +
                        'Tidak ada data yang cocok dengan kata kunci <strong>"' + escapeHtml(q) + '"</strong>' +
                    '</td>';
                table.appendChild(noResultsRow);
            } else {
                noResultsRow.style.display = '';
                var strongEl = noResultsRow.querySelector('strong');
                if (strongEl) strongEl.innerText = '"' + q + '"';
            }
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }

    function syncUrlParam(key, value) {
        if (!window.history || !window.history.replaceState) return;
        var url = new URL(window.location.href);
        if (value && value.trim() !== '') {
            url.searchParams.set(key, value.trim());
        } else {
            url.searchParams.delete(key);
        }
        window.history.replaceState({}, '', url.toString());
    }

    function escapeHtml(string) {
        var entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return String(string).replace(/[&<>"']/g, function (s) {
            return entityMap[s];
        });
    }

    // Jalankan saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInstantSearch);
    } else {
        initInstantSearch();
    }

    // Expose API ke window jika ada komponen dinamis yang memerlukan inisialisasi ulang
    window.initInstantSearch = initInstantSearch;
})();
