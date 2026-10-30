/**
 * Live Instant Search Utility (Context & Role Aware)
 * Zyh Corner Cake Shop
 *
 * Mengaktifkan pencarian instan (as-you-type) tanpa reload / enter,
 * mendukung live AJAX server-side search untuk paginasi multi-page database,
 * dilengkapi tombol clear (x), keyboard shortcut (Escape), dan empty state dinamis.
 */
(function() {
    'use strict';

    var abortControllers = new WeakMap();

    function getAjaxContainer(input) {
        var form = input.closest('form');
        var selector = (form && form.getAttribute('data-ajax-container')) || 
                       input.getAttribute('data-ajax-container') ||
                       (document.getElementById('all-sales-table-container') ? '#all-sales-table-container' : null);
        if (selector) {
            return document.querySelector(selector);
        }
        return null;
    }

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
                    if (debounceTimer) clearTimeout(debounceTimer);
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

            // Hubungkan event pagination jika container AJAX sudah ada di halaman
            var form = input.closest('form');
            var ajaxContainer = getAjaxContainer(input);
            if (ajaxContainer) {
                bindAjaxContainer(ajaxContainer, form, input);
            }

            // Cegah reload form saat pengguna menekan tombol Enter jika pencarian ditangani oleh JavaScript
            if (form) {
                form.addEventListener('submit', function(e) {
                    var container = getAjaxContainer(input);
                    if (container || hasLocalFilterableContent()) {
                        e.preventDefault();
                        if (debounceTimer) clearTimeout(debounceTimer);
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
                var hasAjax = !!getAjaxContainer(input);
                var waitTime = hasAjax ? 250 : 150;
                debounceTimer = setTimeout(function() {
                    triggerFilter(input, q);
                }, waitTime);
            });

            // Keyboard shortcut: Escape membersihkan pencarian
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (this.value.length > 0) {
                        this.value = '';
                        updateClearBtn();
                        if (debounceTimer) clearTimeout(debounceTimer);
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

        // 0. Live AJAX Server-Side Search (Multi-Page Database Search)
        var ajaxContainer = getAjaxContainer(input);
        if (ajaxContainer) {
            triggerAjaxSearch(input, input.closest('form'), ajaxContainer, query);
            return;
        }

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

        // 4. Filter Baris Tabel Lokal (Jika tanpa server-side ajax)
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

    function triggerAjaxSearch(input, form, container, query) {
        var prevController = abortControllers.get(input);
        if (prevController) {
            prevController.abort();
        }
        var controller = new AbortController();
        abortControllers.set(input, controller);

        // Feedback visual: ubah icon kaca pembesar menjadi spinner halus tanpa layout shift
        var icon = input.parentElement ? input.parentElement.querySelector('.mdi-magnify, .mdi-loading') : null;
        if (icon) {
            if (!icon.dataset.originalClass) icon.dataset.originalClass = icon.className;
            icon.className = 'mdi mdi-loading mdi-spin position-absolute text-primary';
        }

        // Zero Layout Shift: opasitas transisi halus tanpa mengubah tinggi kontainer
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        container.style.transition = 'opacity 0.15s ease';

        var targetUrl = new URL(form ? form.action : window.location.href, window.location.origin);
        if (form) {
            var formData = new FormData(form);
            for (var pair of formData.entries()) {
                if (pair[0] !== 'page' && pair[0] !== 'search') {
                    targetUrl.searchParams.set(pair[0], pair[1]);
                }
            }
        }
        if (query && query.trim() !== '') {
            targetUrl.searchParams.set('search', query.trim());
        } else {
            targetUrl.searchParams.delete('search');
        }
        targetUrl.searchParams.delete('page'); // Selalu reset ke halaman 1 saat pencarian baru
        targetUrl.searchParams.set('ajax', '1');

        fetch(targetUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html'
            },
            signal: controller.signal
        })
        .then(function(res) {
            var contentType = res.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return res.json();
            } else {
                return res.text().then(function(t) { return { html: t }; });
            }
        })
        .then(function(data) {
            if (data && data.html) {
                container.innerHTML = data.html;
            }
            // Update KPI Card Numbers jika ada di respon
            if (data && data.totalPendapatan) {
                var omset = document.querySelector('[data-kpi="total-pendapatan"]');
                if (omset) omset.innerText = data.totalPendapatan;
            }
            if (data && data.totalOnlineCount !== undefined) {
                var online = document.querySelector('[data-kpi="online-count"]');
                if (online) online.innerText = data.totalOnlineCount;
            }
            if (data && data.totalOfflineCount !== undefined) {
                var offline = document.querySelector('[data-kpi="offline-count"]');
                if (offline) offline.innerText = data.totalOfflineCount;
            }

            // Sinkronisasi URL address bar tanpa reload penuh
            targetUrl.searchParams.delete('ajax');
            window.history.replaceState({}, '', targetUrl.toString());

            // Re-bind listener pagination & empty state reset
            bindAjaxContainer(container, form, input);
        })
        .catch(function(err) {
            if (err.name === 'AbortError') return; // Request lama dibatalkan karena pengetikan baru
            console.warn('AJAX live search error:', err);
        })
        .finally(function() {
            container.style.opacity = '1';
            container.style.pointerEvents = '';
            if (icon && icon.dataset.originalClass) {
                icon.className = icon.dataset.originalClass;
            }
        });
    }

    function fetchAjaxPage(url, container, form, input) {
        var icon = input && input.parentElement ? input.parentElement.querySelector('.mdi-magnify, .mdi-loading') : null;
        if (icon) {
            if (!icon.dataset.originalClass) icon.dataset.originalClass = icon.className;
            icon.className = 'mdi mdi-loading mdi-spin position-absolute text-primary';
        }

        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        container.style.transition = 'opacity 0.15s ease';

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html'
            }
        })
        .then(function(res) {
            var contentType = res.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return res.json();
            } else {
                return res.text().then(function(t) { return { html: t }; });
            }
        })
        .then(function(data) {
            if (data && data.html) {
                container.innerHTML = data.html;
            }
            if (data && data.totalPendapatan) {
                var omset = document.querySelector('[data-kpi="total-pendapatan"]');
                if (omset) omset.innerText = data.totalPendapatan;
            }
            if (data && data.totalOnlineCount !== undefined) {
                var online = document.querySelector('[data-kpi="online-count"]');
                if (online) online.innerText = data.totalOnlineCount;
            }
            if (data && data.totalOfflineCount !== undefined) {
                var offline = document.querySelector('[data-kpi="offline-count"]');
                if (offline) offline.innerText = data.totalOfflineCount;
            }

            url.searchParams.delete('ajax');
            window.history.replaceState({}, '', url.toString());

            bindAjaxContainer(container, form, input);
        })
        .catch(function(err) {
            console.warn('Gagal memuat halaman AJAX:', err);
        })
        .finally(function() {
            container.style.opacity = '1';
            container.style.pointerEvents = '';
            if (icon && icon.dataset.originalClass) {
                icon.className = icon.dataset.originalClass;
            }
        });
    }

    function bindAjaxContainer(container, form, input) {
        if (!container) return;

        // Reset pencarian pada empty state
        var resetBtns = container.querySelectorAll('.reset-instant-search-btn');
        resetBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (input) {
                    input.value = '';
                    var clearBtn = input.parentElement ? input.parentElement.querySelector('.instant-search-clear-btn') : null;
                    if (clearBtn) clearBtn.style.display = 'none';
                    triggerAjaxSearch(input, form, container, '');
                    input.focus();
                }
            });
        });

        // Intersep pagination links
        var paginationLinks = container.querySelectorAll('.pagination a');
        paginationLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var targetUrl = new URL(this.href, window.location.origin);
                targetUrl.searchParams.set('ajax', '1');
                
                // Halus scroll ke atas kontainer tabel jika sudah ter-scroll jauh
                var rect = container.getBoundingClientRect();
                if (rect.top < 0) {
                    window.scrollTo({
                        top: window.pageYOffset + rect.top - 80,
                        behavior: 'smooth'
                    });
                }

                fetchAjaxPage(targetUrl, container, form, input);
            });
        });
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
