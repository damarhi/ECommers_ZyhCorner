window.currentShippingCost = 12000;

    window.selectShippingOption = function(cardEl) {
        if (!cardEl) return;
        document.querySelectorAll('.shipping-card-option').forEach(el => el.classList.remove('active'));
        cardEl.classList.add('active');
        const radio = cardEl.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        
        const costAttr = cardEl.getAttribute('data-cost');
        const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
        window.currentShippingCost = isNaN(cost) ? 0 : cost;
        window.updateOrderTotalsWithShipping();
    };

    window.selectPaymentMethod = function(method) {
        document.querySelectorAll('.payment-option-item').forEach(el => el.classList.remove('active'));
        const codRadio = document.getElementById('payCod');
        const transferRadio = document.getElementById('payTransfer');
        const ewalletRadio = document.getElementById('payEwallet');
        if (method === 'cod' && codRadio) {
            codRadio.checked = true;
            const parent = codRadio.closest('.payment-option-item');
            if (parent) parent.classList.add('active');
        } else if (method === 'transfer' && transferRadio) {
            transferRadio.checked = true;
            const parent = transferRadio.closest('.payment-option-item');
            if (parent) parent.classList.add('active');
        } else if (method === 'ewallet' && ewalletRadio) {
            ewalletRadio.checked = true;
            const parent = ewalletRadio.closest('.payment-option-item');
            if (parent) parent.classList.add('active');
        }
    };

    window.updateOrderTotalsWithShipping = function() {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        let subtotalVal = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const subtotalEl = document.getElementById('summarySubtotal');
        const shippingEl = document.getElementById('summaryShipping');
        const totalEl = document.getElementById('summaryTotal');
        
        if (subtotalEl) {
            subtotalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal);
        }
        
        const cost = window.currentShippingCost !== undefined ? window.currentShippingCost : 12000;
        if (shippingEl) {
            if (cost === 0) {
                shippingEl.className = 'value free-badge';
                shippingEl.textContent = 'Gratis';
            } else {
                shippingEl.className = 'value';
                shippingEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(cost);
            }
        }
        
        if (totalEl) {
            totalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal + cost);
        }
    };

    window.refreshShippingRates = function(silent) {
        const btn = document.getElementById('btnRefreshShipping');
        const icon = document.getElementById('refreshShippingIcon');
        const text = document.getElementById('refreshShippingText');
        const areaIdEl = document.getElementById('destinationAreaId');
        const areaInput = document.getElementById('areaSearchInput');

        const destinationAreaId = areaIdEl ? areaIdEl.value.trim() : '';
        const destinationAreaName = areaInput ? areaInput.value.trim() : '';

        if (!silent && !destinationAreaId && !destinationAreaName) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Pilih Wilayah Terlebih Dahulu',
                    text: 'Silakan isi kelurahan atau kecamatan tujuan pada Langkah 1 terlebih dahulu untuk mengecek tarif ongkir.',
                    icon: 'info',
                    confirmButtonColor: '#00b207'
                });
            }
            return;
        }

        if (btn) btn.disabled = true;
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin';
        if (text) text.textContent = 'Memuat...';

        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const itemsPayload = localCart.map(item => ({
            name: item.name,
            value: item.price,
            weight: 1000,
            quantity: item.qty || 1
        }));

        fetch(window.checkoutConfig.biteshipRatesRoute || '/biteship/rates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.checkoutConfig.csrfToken
            },
            body: JSON.stringify({
                destination_area_id: destinationAreaId,
                destination_area_name: destinationAreaName,
                items: itemsPayload
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (icon) icon.className = 'fa-solid fa-arrows-rotate';
            if (text) text.textContent = 'Cek Ongkir';

            if (data && data.success && data.couriers) {
                const couriers = data.couriers;
                const formatRupiah = (num) => 'Rp. ' + new Intl.NumberFormat('id-ID').format(num);

                const gojekCard = document.querySelector('.shipping-card-option[data-service="Gojek"]');
                const grabCard = document.querySelector('.shipping-card-option[data-service="Grab"]');
                const maximCard = document.querySelector('.shipping-card-option[data-service="Maxim"]');
                const costGojek = document.getElementById('costGojek');
                const costGrab = document.getElementById('costGrab');
                const costMaxim = document.getElementById('costMaxim');

                if (gojekCard && couriers.Gojek !== undefined) {
                    gojekCard.setAttribute('data-cost', couriers.Gojek);
                    if (costGojek) costGojek.textContent = formatRupiah(couriers.Gojek);
                }
                if (grabCard && couriers.Grab !== undefined) {
                    grabCard.setAttribute('data-cost', couriers.Grab);
                    if (costGrab) costGrab.textContent = formatRupiah(couriers.Grab);
                }
                if (maximCard && couriers.Maxim !== undefined) {
                    maximCard.setAttribute('data-cost', couriers.Maxim);
                    if (costMaxim) costMaxim.textContent = formatRupiah(couriers.Maxim);
                }

                // Recalculate currently active shipping option
                const activeCard = document.querySelector('.shipping-card-option.active');
                if (activeCard) {
                    const costAttr = activeCard.getAttribute('data-cost');
                    const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
                    window.currentShippingCost = isNaN(cost) ? 0 : cost;
                    window.updateOrderTotalsWithShipping();
                }

                if (!silent && typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Tarif ongkir berhasil diperbarui'
                    });
                }
            }
        })
        .catch(err => {
            console.error('Failed to refresh shipping rates:', err);
            if (btn) btn.disabled = false;
            if (icon) icon.className = 'fa-solid fa-arrows-rotate';
            if (text) text.textContent = 'Cek Ongkir';
        });
    };
    window.toggleAddressLabel = function(chk) {
        const wrapper = document.getElementById('addressLabelWrapper');
        if (wrapper) {
            wrapper.style.display = chk.checked ? 'block' : 'none';
            if (chk.checked) {
                const inp = document.getElementById('newAddressLabel');
                if (inp && !inp.value) inp.value = 'Alamat Rumah';
            }
        }
    };
    window.toggleAddressAccordion = function(forceOpen) {
        const acc = document.getElementById('checkoutAddressAccordion');
        const body = document.getElementById('addressAccordionBody');
        const label = document.getElementById('accordionToggleLabel');
        const header = document.getElementById('addressAccordionHeader');
        if (!acc || !body) return;

        const isCurrentlyOpen = acc.classList.contains('open');
        const shouldOpen = (forceOpen !== undefined) ? forceOpen : !isCurrentlyOpen;

        if (shouldOpen) {
            acc.classList.add('open');
            body.style.maxHeight = '2500px';
            if (label) label.textContent = 'Tutup Detail';
            if (header) header.setAttribute('aria-expanded', 'true');

            // Invalidate Leaflet map layout after animation
            setTimeout(() => {
                if (typeof checkoutMap !== 'undefined' && checkoutMap) {
                    checkoutMap.invalidateSize();
                } else if (typeof window.checkoutMap !== 'undefined' && window.checkoutMap) {
                    window.checkoutMap.invalidateSize();
                }
            }, 350);
        } else {
            acc.classList.remove('open');
            body.style.maxHeight = '0px';
            if (label) label.textContent = 'Detail Alamat';
            if (header) header.setAttribute('aria-expanded', 'false');
        }
    };

    window.updateSummaryPreview = function(name, street, hasGps) {
        const sumName = document.getElementById('summaryRecipientName');
        const sumStreet = document.getElementById('summaryStreetAddress');
        const sumGps = document.getElementById('summaryGpsBadge');

        if (sumName && name) sumName.textContent = name;
        if (sumStreet && street) sumStreet.textContent = street;
        if (sumGps) {
            sumGps.style.display = hasGps ? 'inline-flex' : 'none';
        }
    };

    window.onSavedAddressDropdownChange = function(selectEl) {
        if (!selectEl) return;
        const val = selectEl.value;

        if (val === 'new') {
            window.selectNewAddress();
        } else {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (opt && opt.dataset.address) {
                try {
                    const addr = JSON.parse(opt.dataset.address);
                    window.selectSavedAddress(addr);
                } catch (e) {
                    console.error("Gagal parse data alamat", e);
                }
            }
        }
    };

    window.selectSavedAddress = function(addr, cardEl) {
        if (!addr) return;
        document.querySelectorAll('.saved-address-card').forEach(c => c.classList.remove('active'));
        if (cardEl) cardEl.classList.add('active');

        const selectEl = document.getElementById('savedAddressSelect');
        if (selectEl && addr.id && selectEl.value !== String(addr.id)) {
            selectEl.value = String(addr.id);
        }

        const nameEl = document.getElementById('recipientName');
        const phoneEl = document.getElementById('recipientPhone');
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const streetEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        const coordsDisplay = document.getElementById('coordsDisplay');
        const saveGroup = document.getElementById('saveAddressGroup');

        if (nameEl) nameEl.value = addr.recipient_name || '';
        if (phoneEl) phoneEl.value = addr.recipient_phone || '';
        if (areaInput) areaInput.value = addr.destination_area_name || '';
        if (areaIdEl) areaIdEl.value = addr.destination_area_id || '';
        if (postalEl) postalEl.value = addr.postal_code || '';
        if (streetEl) streetEl.value = addr.street_address || '';
        if (notesEl && addr.notes) notesEl.value = addr.notes;

        const hasGps = !!(addr.latitude && addr.longitude);
        if (hasGps) {
            if (latEl) latEl.value = addr.latitude;
            if (lngEl) lngEl.value = addr.longitude;
            if (coordsDisplay) coordsDisplay.textContent = `Titik koordinat tersimpan: ${parseFloat(addr.latitude).toFixed(5)}, ${parseFloat(addr.longitude).toFixed(5)}`;
            if (typeof checkoutMarker !== 'undefined' && checkoutMarker && typeof checkoutMap !== 'undefined' && checkoutMap) {
                const pos = [parseFloat(addr.latitude), parseFloat(addr.longitude)];
                checkoutMarker.setLatLng(pos);
                checkoutMap.setView(pos, 15);
            }
        } else {
            if (latEl) latEl.value = '';
            if (lngEl) lngEl.value = '';
            if (coordsDisplay) coordsDisplay.textContent = 'Klik peta atau seret marker untuk menandai lokasi tepat Anda';
        }
        if (typeof window.clearCheckoutMapSearch === 'function') {
            window.clearCheckoutMapSearch();
        }

        window.updateSummaryPreview(addr.recipient_name, addr.street_address, hasGps);

        if (saveGroup) {
            saveGroup.style.display = 'none';
            const chk = document.getElementById('saveAddressCheckbox');
            if (chk) chk.checked = false;
            const labelWrap = document.getElementById('addressLabelWrapper');
            if (labelWrap) labelWrap.style.display = 'none';
        }
        if (typeof window.refreshShippingRates === 'function') {
            window.refreshShippingRates(true);
        }
    };

    window.selectNewAddress = function(cardEl) {
        document.querySelectorAll('.saved-address-card').forEach(c => c.classList.remove('active'));
        if (cardEl) cardEl.classList.add('active');

        const selectEl = document.getElementById('savedAddressSelect');
        if (selectEl && selectEl.value !== 'new') {
            selectEl.value = 'new';
        }
        if (typeof window.clearCheckoutMapSearch === 'function') {
            window.clearCheckoutMapSearch();
        }

        const nameEl = document.getElementById('recipientName');
        const phoneEl = document.getElementById('recipientPhone');
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const streetEl = document.getElementById('streetAddress');
        const saveGroup = document.getElementById('saveAddressGroup');

        if (nameEl) nameEl.value = (window.checkoutConfig && window.checkoutConfig.defaultUserNama) || '';
        if (phoneEl) phoneEl.value = (window.checkoutConfig && window.checkoutConfig.defaultUserNoHp) || '';
        if (areaInput) areaInput.value = '';
        if (areaIdEl) areaIdEl.value = '';
        if (postalEl) postalEl.value = '';
        if (streetEl) {
            streetEl.value = '';
        }
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        const coordsDisplay = document.getElementById('coordsDisplay');
        if (latEl) latEl.value = '';
        if (lngEl) lngEl.value = '';
        if (coordsDisplay) coordsDisplay.textContent = 'Klik peta atau seret marker untuk menandai lokasi tepat Anda';

        window.updateSummaryPreview('Alamat Baru', 'Masukkan rincian alamat baru...', false);

        if (saveGroup) {
            saveGroup.style.display = 'block';
            const chk = document.getElementById('saveAddressCheckbox');
            if (chk) {
                chk.checked = true;
                window.toggleAddressLabel(chk);
            }
        }

        window.toggleAddressAccordion(true);
        if (streetEl) streetEl.focus();
    };
    document.addEventListener('DOMContentLoaded', function() {
        renderCustomTextCard();
        initAreaAutocomplete();
        window.updateOrderTotalsWithShipping();

        const acc = document.getElementById('checkoutAddressAccordion');
        const body = document.getElementById('addressAccordionBody');
        if (acc && body) {
            if (acc.classList.contains('open')) {
                body.style.maxHeight = '2500px';
            } else {
                body.style.maxHeight = '0px';
            }
        }
    });

    function initAreaAutocomplete() {
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdInput = document.getElementById('destinationAreaId');
        const postalInput = document.getElementById('postalCode');
        const resultsContainer = document.getElementById('areaDropdownResults');
        if (!areaInput || !resultsContainer) return;

        const sampleAreas = [
            { id: 'ID_PLW_01', name: 'Pangkalan Kerinci Kota', city: 'Pangkalan Kerinci, Pelalawan, Riau', postal: '28654' },
            { id: 'ID_PLW_02', name: 'Pangkalan Kerinci Barat', city: 'Pangkalan Kerinci, Pelalawan, Riau', postal: '28381' },
            { id: 'ID_PLW_03', name: 'Pangkalan Kerinci Timur', city: 'Pangkalan Kerinci, Pelalawan, Riau', postal: '28654' },
            { id: 'ID_PLW_04', name: 'Kuala Terusan', city: 'Pangkalan Kerinci, Pelalawan, Riau', postal: '28654' },
            { id: 'ID_PLW_05', name: 'Makmur', city: 'Pangkalan Kerinci, Pelalawan, Riau', postal: '28654' },
            { id: 'ID_PLW_06', name: 'Bandar Seikijang', city: 'Pelalawan, Riau', postal: '28383' },
            { id: 'ID_PLW_07', name: 'Pangkalan Kuras / Sorek', city: 'Pelalawan, Riau', postal: '28382' },
            { id: 'ID_PKU_01', name: 'Sukajadi', city: 'Pekanbaru, Riau', postal: '28121' },
            { id: 'ID_PKU_02', name: 'Tampan / Tuah Madani', city: 'Pekanbaru, Riau', postal: '28292' },
            { id: 'ID_PKU_03', name: 'Marpoyan Damai', city: 'Pekanbaru, Riau', postal: '28282' },
            { id: 'ID_PKU_04', name: 'Bukit Raya', city: 'Pekanbaru, Riau', postal: '28288' },
            { id: 'ID_PKU_05', name: 'Payung Sekaki', city: 'Pekanbaru, Riau', postal: '28291' },
            { id: 'ID_PKU_06', name: 'Rumbai / Rumbai Pesisir', city: 'Pekanbaru, Riau', postal: '28261' },
            { id: 'ID_PKU_07', name: 'Tenayan Raya / Kulim', city: 'Pekanbaru, Riau', postal: '28285' },
            { id: 'ID_PKU_08', name: 'Senapelan', city: 'Pekanbaru, Riau', postal: '28153' },
            { id: 'ID_PKU_09', name: 'Limapuluh', city: 'Pekanbaru, Riau', postal: '28144' },
            { id: 'ID_PKU_10', name: 'Bina Widya', city: 'Pekanbaru, Riau', postal: '28293' }
        ];

        let areaDebounceTimer = null;

        function renderAreaResults(items, query) {
            if (!items || items.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="area-dropdown-item" style="color: #64748b; cursor: default;">
                        Wilayah tidak ditemukan di daftar cepat. Anda dapat mengetik manual.
                    </div>
                `;
                resultsContainer.style.display = 'block';
                if (areaIdInput) areaIdInput.value = 'CUSTOM_' + query.replace(/[^a-zA-Z0-9]/g, '_').toUpperCase();
                return;
            }

            resultsContainer.innerHTML = items.map(area => `
                <div class="area-dropdown-item" onclick="selectArea('${area.id}', '${area.name}, ${area.city}', '${area.postal}')">
                    <strong>${area.name}</strong>
                    <div class="area-detail">${area.city} &bull; Kode Pos: ${area.postal}</div>
                </div>
            `).join('');
            resultsContainer.style.display = 'block';
        }

        areaInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            clearTimeout(areaDebounceTimer);
            areaDebounceTimer = setTimeout(() => {
                fetch(`${(window.checkoutConfig && window.checkoutConfig.biteshipAreasRoute) || '/biteship/areas'}?search=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            renderAreaResults(data, query);
                        } else {
                            const qLower = query.toLowerCase();
                            const matches = sampleAreas.filter(a => 
                                a.name.toLowerCase().includes(qLower) || 
                                a.city.toLowerCase().includes(qLower) || 
                                a.postal.includes(qLower)
                            );
                            renderAreaResults(matches, query);
                        }
                    })
                    .catch(() => {
                        const qLower = query.toLowerCase();
                        const matches = sampleAreas.filter(a => 
                            a.name.toLowerCase().includes(qLower) || 
                            a.city.toLowerCase().includes(qLower) || 
                            a.postal.includes(qLower)
                        );
                        renderAreaResults(matches, query);
                    });
            }, 250);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!areaInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    window.selectArea = function(id, name, postal) {
        const areaInput = document.getElementById('areaSearchInput');
        const areaIdInput = document.getElementById('destinationAreaId');
        const postalInput = document.getElementById('postalCode');
        const resultsContainer = document.getElementById('areaDropdownResults');

        if (areaInput) areaInput.value = name;
        if (areaIdInput) areaIdInput.value = id;
        if (postalInput) postalInput.value = postal;
        if (resultsContainer) resultsContainer.style.display = 'none';
        if (typeof window.refreshShippingRates === 'function') {
            window.refreshShippingRates(true);
        }
    };
    function renderCustomTextCard() {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const card = document.getElementById('customCakeTextCard');
        const container = document.getElementById('customTextInputsContainer');
        if (!card || !container) return;

        const serverIds = (window.checkoutConfig && window.checkoutConfig.customTextProductIds) ? window.checkoutConfig.customTextProductIds : [];

        // Sync flags for items in localCart
        localCart.forEach(item => {
            if (serverIds.includes(String(item.id))) {
                item.bisa_custom_teks = true;
            }
        });

        const customItems = localCart.filter(item => 
            item.bisa_custom_teks === true || 
            item.bisa_custom_teks === 1 || 
            item.bisa_custom_teks === '1' || 
            item.bisa_custom_teks === 'true' ||
            serverIds.includes(String(item.id))
        );
        
        if (customItems.length === 0) {
            card.style.display = 'none';
            return;
        }

        card.style.display = 'block';
        container.innerHTML = customItems.map(item => `
            <div class="form-group-custom" style="margin-bottom: 15px;">
                <label for="custom_text_${item.id}" style="font-weight: 600; color: #1a1a1a; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-cake-candles" style="color: #00b207;"></i> Tulisan di Atas Kue: <span style="color: #00b207;">${item.name}</span>
                </label>
                <input type="text" id="custom_text_${item.id}" class="form-input-custom custom-text-field"
                    placeholder="Contoh: Selamat Wisuda / Sukses Selalu / Terima Kasih"
                    value="${item.custom_text || ''}"
                    onchange="saveCustomTextToCart('${item.id}', this.value)">
            </div>
        `).join('');
    }

    window.saveCustomTextToCart = function(id, text) {
        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        const item = localCart.find(i => i.id === id);
        if (item) {
            item.custom_text = text.trim();
            localStorage.setItem('zyh_cart', JSON.stringify(localCart));
        }
    };

    // Override window.submitOrder to integrate Midtrans Snap
    window.submitOrder = function() {
        const form = document.getElementById('checkoutForm');
        if (form && !form.checkValidity()) {
            window.toggleAddressAccordion(true);
            setTimeout(() => {
                form.reportValidity();
            }, 150);
            return;
        }

        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        if (localCart.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Keranjang belanja kosong!',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        // Collect custom text values from inputs
        localCart.forEach(item => {
            const input = document.getElementById('custom_text_' + item.id);
            if (input) {
                item.custom_text = input.value.trim();
            }
        });
        localStorage.setItem('zyh_cart', JSON.stringify(localCart));

        const recipientNameEl = document.getElementById('recipientName');
        const recipientPhoneEl = document.getElementById('recipientPhone');
        const areaIdEl = document.getElementById('destinationAreaId');
        const postalEl = document.getElementById('postalCode');
        const addrEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');

        let recipientName = recipientNameEl ? recipientNameEl.value.trim() : '';
        let recipientPhone = recipientPhoneEl ? recipientPhoneEl.value.trim() : '';
        let destinationAreaId = areaIdEl ? areaIdEl.value.trim() : '';
        let postalCode = postalEl ? postalEl.value.trim() : '';
        let streetAddress = addrEl ? addrEl.value : '';
        let notes = notesEl ? notesEl.value : '';
        let latitudeVal = latEl && latEl.value ? parseFloat(latEl.value) : null;
        let longitudeVal = lngEl && lngEl.value ? parseFloat(lngEl.value) : null;
        const payRadio = document.querySelector('input[name="paymentGroup"]:checked');
        const shipRadio = document.querySelector('input[name="shippingGroup"]:checked');
        let paymentVal = payRadio ? payRadio.value : 'COD';
        let shippingVal = shipRadio ? shipRadio.value : 'Maxim';
        let shippingCost = window.currentShippingCost !== undefined ? window.currentShippingCost : 20000;

        Swal.fire({
            title: 'Memproses Pesanan...',
            text: 'Harap tunggu sementara kami memverifikasi pesanan Anda.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const routeUrl = window.checkoutConfig.checkoutPostRoute;
        const token = window.checkoutConfig.csrfToken;

        fetch(routeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                items: localCart,
                address: streetAddress,
                notes: notes,
                payment_method: paymentVal,
                shipping_method: shippingVal,
                recipient_name: recipientName,
                recipient_phone: recipientPhone,
                destination_area_id: destinationAreaId,
                destination_area_name: document.getElementById('areaSearchInput')?.value || '',
                postal_code: postalCode,
                shipping_cost: shippingCost,
                save_address: document.getElementById('saveAddressCheckbox')?.checked || false,
                address_label: document.getElementById('newAddressLabel')?.value.trim() || '',
                latitude: latitudeVal,
                longitude: longitudeVal
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Save cart to zyh_last_order for order success display
                localStorage.setItem('zyh_last_order', JSON.stringify(localCart));

                // Clear the cart locally immediately since order is created
                localStorage.removeItem('zyh_cart');
                if (typeof window.updateHeaderCart === 'function') {
                    window.cart = [];
                    window.updateHeaderCart();
                }

                // Redirect directly to the order success page (the customer can complete payment there)
                window.location.href = window.checkoutConfig.orderSuccessRoute + '?order_id=' + data.order.id;
            } else {
                Swal.fire({
                    title: 'Kesalahan!',
                    text: data.message || 'Terjadi kesalahan saat memproses pesanan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal. Harap coba lagi.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const destinationAreaId = document.getElementById('destinationAreaId')?.value;
        const destinationAreaName = document.getElementById('areaSearchInput')?.value;
        if (destinationAreaId || destinationAreaName) {
            if (typeof window.refreshShippingRates === 'function') {
                window.refreshShippingRates(true);
            }
        }
    });
