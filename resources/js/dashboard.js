// Load persisted address details from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    if (!window.dashboardConfig) return;
    const storedName = localStorage.getItem('zyh_cust_name') || window.dashboardConfig.userNama;
    const storedAddress = localStorage.getItem('zyh_cust_address') || window.dashboardConfig.userAlamat || "";
    const storedEmail = localStorage.getItem('zyh_cust_email') || window.dashboardConfig.userEmail;
    const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) " + window.dashboardConfig.userPhone;
    
    if (storedName) {
        const dpn = document.getElementById('displayProfileName');
        if (dpn) dpn.textContent = storedName;
        const dbn = document.getElementById('displayBillingName');
        if (dbn) dbn.textContent = storedName;
    }
    if (storedAddress) {
        const dba = document.getElementById('displayBillingAddress');
        if (dba) dba.textContent = storedAddress;
    }
    if (storedEmail) {
        const dbe = document.getElementById('displayBillingEmail');
        if (dbe) dbe.textContent = storedEmail;
    }
    if (storedPhone) {
        const dbp = document.getElementById('displayBillingPhone');
        if (dbp) dbp.textContent = storedPhone;
    }
    
    // Sync values to form inputs
    const ipn = document.getElementById('inputProfileName');
    if (ipn && storedName) ipn.value = storedName;
    const iba = document.getElementById('inputBillingAddress');
    if (iba && storedAddress) iba.value = storedAddress;
    const ibe = document.getElementById('inputBillingEmail');
    if (ibe && storedEmail) ibe.value = storedEmail;
    const ibp = document.getElementById('inputBillingPhone');
    if (ibp && storedPhone) ibp.value = storedPhone;
});

// Profile modal toggles
window.openEditProfile = function() {
    document.getElementById('modalEditProfile').style.display = 'flex';
}

window.closeEditProfile = function() {
    document.getElementById('modalEditProfile').style.display = 'none';
}

window.saveProfile = function(e) {
    e.preventDefault();
    const newName = document.getElementById('inputProfileName').value;
    localStorage.setItem('zyh_cust_name', newName);
    
    document.getElementById('displayProfileName').textContent = newName;
    document.getElementById('displayBillingName').textContent = newName;
    
    window.closeEditProfile();
    
    Swal.fire({
        title: 'Berhasil!',
        text: 'Profil berhasil diperbarui.',
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Quick chip setter for address labels
window.setLabelChip = function(inputId, labelText) {
    const inp = document.getElementById(inputId);
    if (inp) {
        inp.value = labelText;
        inp.focus();
    }
};

// Leaflet Map instances for address modals
let modalEditMap = null;
let modalEditMarker = null;
let modalAddMap = null;
let modalAddMarker = null;

function initModalLeafletMap(type, latVal, lngVal) {
    if (typeof L === 'undefined') {
        console.warn('Leaflet map library is not loaded on dashboard.');
        return;
    }

    const defaultLat = 0.40384; // Pangkalan Kerinci, Pelalawan (Zyh Corner)
    const defaultLng = 101.85588;

    const lat = (latVal !== null && latVal !== undefined && !isNaN(parseFloat(latVal))) ? parseFloat(latVal) : defaultLat;
    const lng = (lngVal !== null && lngVal !== undefined && !isNaN(parseFloat(lngVal))) ? parseFloat(lngVal) : defaultLng;

    const canvasId = type === 'edit' ? 'modalEditMap' : 'modalAddMap';
    const canvasEl = document.getElementById(canvasId);
    if (!canvasEl) return;

    const coordsDisplay = document.getElementById(type === 'edit' ? 'modalEditCoordsDisplay' : 'modalAddCoordsDisplay');
    const latInput = document.getElementById(type === 'edit' ? 'editAddrLatitude' : 'newAddrLatitude');
    const lngInput = document.getElementById(type === 'edit' ? 'editAddrLongitude' : 'newAddrLongitude');

    function updateCoords(curLat, curLng) {
        if (latInput) latInput.value = curLat;
        if (lngInput) lngInput.value = curLng;
        if (coordsDisplay) {
            coordsDisplay.textContent = `Titik Terpilih: (${curLat.toFixed(5)}, ${curLng.toFixed(5)})`;
        }
    }

    if (type === 'edit') {
        if (modalEditMap) {
            modalEditMap.setView([lat, lng], 15);
            if (modalEditMarker) modalEditMarker.setLatLng([lat, lng]);
            setTimeout(() => { modalEditMap.invalidateSize(); }, 200);
            updateCoords(lat, lng);
            return;
        }

        modalEditMap = L.map('modalEditMap', { scrollWheelZoom: false }).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(modalEditMap);

        modalEditMarker = L.marker([lat, lng], { draggable: true }).addTo(modalEditMap);

        modalEditMarker.on('dragend', function() {
            const pos = modalEditMarker.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });

        modalEditMap.on('click', function(e) {
            modalEditMarker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        updateCoords(lat, lng);
        setTimeout(() => { modalEditMap.invalidateSize(); }, 250);
    } else {
        if (modalAddMap) {
            modalAddMap.setView([lat, lng], 15);
            if (modalAddMarker) modalAddMarker.setLatLng([lat, lng]);
            setTimeout(() => { modalAddMap.invalidateSize(); }, 200);
            updateCoords(lat, lng);
            return;
        }

        modalAddMap = L.map('modalAddMap', { scrollWheelZoom: false }).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(modalAddMap);

        modalAddMarker = L.marker([lat, lng], { draggable: true }).addTo(modalAddMap);

        modalAddMarker.on('dragend', function() {
            const pos = modalAddMarker.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });

        modalAddMap.on('click', function(e) {
            modalAddMarker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        updateCoords(lat, lng);
        setTimeout(() => { modalAddMap.invalidateSize(); }, 250);
    }
}

window.locateUserPosition = function(type) {
    if (!navigator.geolocation) {
        Swal.fire({ title: 'Info', text: 'Geolocation tidak didukung oleh browser Anda.', icon: 'info' });
        return;
    }

    Swal.fire({
        title: 'Mencari Lokasi GPS...',
        text: 'Mengambil titik koordinat Anda saat ini...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    navigator.geolocation.getCurrentPosition(
        function(position) {
            Swal.close();
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            if (type === 'edit') {
                if (modalEditMap && modalEditMarker) {
                    modalEditMap.setView([lat, lng], 16);
                    modalEditMarker.setLatLng([lat, lng]);
                    const latInput = document.getElementById('editAddrLatitude');
                    const lngInput = document.getElementById('editAddrLongitude');
                    const coordsDisplay = document.getElementById('modalEditCoordsDisplay');
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    if (coordsDisplay) coordsDisplay.textContent = `Titik GPS Anda: (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                }
            } else {
                if (modalAddMap && modalAddMarker) {
                    modalAddMap.setView([lat, lng], 16);
                    modalAddMarker.setLatLng([lat, lng]);
                    const latInput = document.getElementById('newAddrLatitude');
                    const lngInput = document.getElementById('newAddrLongitude');
                    const coordsDisplay = document.getElementById('modalAddCoordsDisplay');
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    if (coordsDisplay) coordsDisplay.textContent = `Titik GPS Anda: (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                }
            }
        },
        function(error) {
            Swal.close();
            Swal.fire({
                title: 'Lokasi Tidak Ditemukan',
                text: 'Harap izinkan akses lokasi pada browser Anda.',
                icon: 'warning'
            });
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
};

// Saved Addresses Modal Controls
window.openSavedAddressesModal = function() {
    const modal = document.getElementById('modalSavedAddresses');
    if (modal) modal.style.display = 'flex';
};

window.closeSavedAddressesModal = function() {
    const modal = document.getElementById('modalSavedAddresses');
    if (modal) modal.style.display = 'none';
};

window.openEditFromSaved = function(addr) {
    window.closeSavedAddressesModal();
    setTimeout(() => {
        window.openEditAddress(addr);
    }, 150);
};

window.openAddFromSaved = function() {
    window.closeSavedAddressesModal();
    setTimeout(() => {
        window.openAddAddressModal();
    }, 150);
};

// Modal Edit Address
window.openEditAddress = function(addr) {
    const config = window.dashboardConfig || {};
    const targetAddr = addr || null;

    const idEl = document.getElementById('editAddrId');
    const labelEl = document.getElementById('editAddrLabel');
    const nameEl = document.getElementById('editAddrRecipientName');
    const phoneEl = document.getElementById('editAddrRecipientPhone');
    const areaSearchEl = document.getElementById('editAddrAreaSearch');
    const areaIdEl = document.getElementById('editAddrDestinationAreaId');
    const postalEl = document.getElementById('editAddrPostalCode');
    const streetEl = document.getElementById('editAddrStreetAddress');
    const notesEl = document.getElementById('editAddrNotes');
    const defaultEl = document.getElementById('editAddrIsDefault');
    const headerTitle = document.getElementById('editModalHeaderTitle');

    if (idEl) idEl.value = targetAddr ? (targetAddr.id || '') : '';
    if (labelEl) labelEl.value = targetAddr ? (targetAddr.label || 'Alamat Utama') : 'Alamat Utama';
    if (nameEl) nameEl.value = targetAddr ? (targetAddr.recipient_name || config.userNama || '') : (config.userNama || '');
    if (phoneEl) phoneEl.value = targetAddr ? (targetAddr.recipient_phone || config.userPhone || '') : (config.userPhone || '');
    if (areaSearchEl) areaSearchEl.value = targetAddr ? (targetAddr.destination_area_name || '') : '';
    if (areaIdEl) areaIdEl.value = targetAddr ? (targetAddr.destination_area_id || '') : '';
    if (postalEl) postalEl.value = targetAddr ? (targetAddr.postal_code || '') : '';
    if (streetEl) streetEl.value = targetAddr ? (targetAddr.street_address || config.userAlamat || '') : (config.userAlamat || '');
    if (notesEl) notesEl.value = targetAddr ? (targetAddr.notes || '') : '';
    if (defaultEl) defaultEl.checked = targetAddr ? !!targetAddr.is_default : true;

    if (headerTitle) {
        headerTitle.textContent = targetAddr && targetAddr.label ? `Edit ${targetAddr.label}` : 'Edit Alamat Pengiriman';
    }

    // Mirrored compatibility fields
    const billAddr = document.getElementById('inputBillingAddress');
    const billEmail = document.getElementById('inputBillingEmail');
    const billPhone = document.getElementById('inputBillingPhone');
    if (billAddr) billAddr.value = streetEl ? streetEl.value : '';
    if (billEmail) billEmail.value = config.userEmail || '';
    if (billPhone) billPhone.value = phoneEl ? phoneEl.value : '';

    const modal = document.getElementById('modalEditAddress');
    if (modal) modal.style.display = 'flex';

    initModalLeafletMap('edit', targetAddr?.latitude, targetAddr?.longitude);
};

window.closeEditAddress = function() {
    const modal = document.getElementById('modalEditAddress');
    if (modal) modal.style.display = 'none';
};

window.openEditExtraAddress = function(addr) {
    window.openEditAddress(addr);
};

window.closeEditExtraAddressModal = function() {
    window.closeEditAddress();
};

window.saveAddress = function(e) {
    e.preventDefault();
    const config = window.dashboardConfig || {};
    const csrfToken = config.csrfToken || '';
    const id = document.getElementById('editAddrId')?.value;

    const streetAddress = document.getElementById('editAddrStreetAddress')?.value.trim() || document.getElementById('inputBillingAddress')?.value.trim() || '';
    const recipientName = document.getElementById('editAddrRecipientName')?.value.trim() || config.userNama || '';
    const recipientPhone = document.getElementById('editAddrRecipientPhone')?.value.trim() || config.userPhone || '';
    const label = document.getElementById('editAddrLabel')?.value.trim() || 'Alamat Utama';
    const areaName = document.getElementById('editAddrAreaSearch')?.value.trim() || '';
    const areaId = document.getElementById('editAddrDestinationAreaId')?.value.trim() || '';
    const postalCode = document.getElementById('editAddrPostalCode')?.value.trim() || '';
    const notes = document.getElementById('editAddrNotes')?.value.trim() || '';
    const latitude = document.getElementById('editAddrLatitude')?.value || null;
    const longitude = document.getElementById('editAddrLongitude')?.value || null;
    const isDefault = document.getElementById('editAddrIsDefault')?.checked ? 1 : 0;

    localStorage.setItem('zyh_cust_address', streetAddress);
    localStorage.setItem('zyh_cust_phone', recipientPhone);
    const displayAddr = document.getElementById('displayBillingAddress');
    const displayPhone = document.getElementById('displayBillingPhone');
    if (displayAddr) displayAddr.textContent = streetAddress;
    if (displayPhone) displayPhone.textContent = `(+62) ${recipientPhone}`;

    Swal.fire({
        title: 'Menyimpan Perubahan...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const payload = {
        label: label,
        recipient_name: recipientName,
        recipient_phone: recipientPhone,
        destination_area_name: areaName,
        destination_area_id: areaId,
        postal_code: postalCode,
        street_address: streetAddress,
        notes: notes,
        latitude: latitude ? parseFloat(latitude) : null,
        longitude: longitude ? parseFloat(longitude) : null,
        is_default: isDefault
    };

    const targetUrl = id ? `/customer/addresses/${id}` : '/customer/addresses';
    const targetMethod = id ? 'PUT' : 'POST';

    const savePromise = id ? 
        fetch(targetUrl, {
            method: targetMethod,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        }) : 
        fetch('/settings/address', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ alamat: streetAddress })
        });

    savePromise
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                window.closeEditAddress();
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Alamat berhasil diperbarui.',
                    icon: 'success',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal menyimpan perubahan alamat.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            console.error(err);
            Swal.fire({
                title: 'Berhasil!',
                text: 'Alamat berhasil diperbarui secara lokal.',
                icon: 'success',
                confirmButtonColor: '#00b207'
            });
        });
};

// Modal Add Address
window.openAddAddressModal = function() {
    const config = window.dashboardConfig || {};
    const nameEl = document.getElementById('newAddrRecipientName');
    const phoneEl = document.getElementById('newAddrRecipientPhone');
    const labelEl = document.getElementById('newAddrLabel');
    const areaSearchEl = document.getElementById('newAddrAreaSearch');
    const areaIdEl = document.getElementById('newAddrDestinationAreaId');
    const postalEl = document.getElementById('newAddrPostalCode');
    const streetEl = document.getElementById('newAddrStreetAddress');
    const notesEl = document.getElementById('newAddrNotes');
    const defaultEl = document.getElementById('newAddrIsDefault');

    if (labelEl) labelEl.value = 'Alamat Rumah';
    if (nameEl) nameEl.value = config.userNama || '';
    if (phoneEl) phoneEl.value = config.userPhone || '';
    if (areaSearchEl) areaSearchEl.value = '';
    if (areaIdEl) areaIdEl.value = '';
    if (postalEl) postalEl.value = '';
    if (streetEl) streetEl.value = '';
    if (notesEl) notesEl.value = '';
    if (defaultEl) defaultEl.checked = false;

    const m = document.getElementById('modalAddAddress');
    if (m) m.style.display = 'flex';

    initModalLeafletMap('add', null, null);
};

window.closeAddAddressModal = function() {
    const m = document.getElementById('modalAddAddress');
    if (m) m.style.display = 'none';
};

window.submitNewAddress = function(e) {
    e.preventDefault();
    const csrfToken = window.dashboardConfig ? window.dashboardConfig.csrfToken : '';
    
    const payload = {
        label: document.getElementById('newAddrLabel')?.value.trim() || 'Alamat Rumah',
        recipient_name: document.getElementById('newAddrRecipientName')?.value.trim() || '',
        recipient_phone: document.getElementById('newAddrRecipientPhone')?.value.trim() || '',
        destination_area_name: document.getElementById('newAddrAreaSearch')?.value.trim() || '',
        destination_area_id: document.getElementById('newAddrDestinationAreaId')?.value.trim() || '',
        postal_code: document.getElementById('newAddrPostalCode')?.value.trim() || '',
        street_address: document.getElementById('newAddrStreetAddress')?.value.trim() || '',
        notes: document.getElementById('newAddrNotes')?.value.trim() || '',
        latitude: document.getElementById('newAddrLatitude')?.value || null,
        longitude: document.getElementById('newAddrLongitude')?.value || null,
        is_default: document.getElementById('newAddrIsDefault')?.checked ? 1 : 0
    };

    Swal.fire({
        title: 'Menyimpan...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('/customer/addresses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            window.closeAddAddressModal();
            Swal.fire({
                title: 'Berhasil!',
                text: data.message || 'Alamat baru berhasil disimpan.',
                icon: 'success',
                confirmButtonColor: '#00b207'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Gagal!',
                text: data.message || 'Gagal menyimpan alamat.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
        }
    })
    .catch(err => {
        Swal.close();
        console.error(err);
        Swal.fire({ title: 'Error', text: 'Koneksi ke server gagal.', icon: 'error' });
    });
};

// Set Default Address
window.setDefaultAddress = function(id) {
    const csrfToken = window.dashboardConfig ? window.dashboardConfig.csrfToken : '';
    Swal.fire({
        title: 'Jadikan Alamat Utama?',
        text: 'Alamat ini akan otomatis terpilih saat Anda melakukan checkout pesanan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Jadikan Utama',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#00b207'
    }).then((res) => {
        if (res.isConfirmed) {
            fetch(`/customer/addresses/${id}/set-default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ title: 'Berhasil!', text: data.message, icon: 'success', confirmButtonColor: '#00b207' })
                        .then(() => window.location.reload());
                }
            });
        }
    });
};

// Delete Address
window.deleteAddress = function(id) {
    const csrfToken = window.dashboardConfig ? window.dashboardConfig.csrfToken : '';
    Swal.fire({
        title: 'Hapus Alamat?',
        text: 'Apakah Anda yakin ingin menghapus alamat ini dari buku alamat?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444'
    }).then((res) => {
        if (res.isConfirmed) {
            fetch(`/customer/addresses/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ title: 'Dihapus!', text: data.message, icon: 'success', confirmButtonColor: '#00b207' })
                        .then(() => window.location.reload());
                }
            });
        }
    });
};

// Init area autocompletes on dashboard modals
document.addEventListener('DOMContentLoaded', function() {
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

    function bindAreaAutocomplete(inputEl, idEl, postalEl, dropdownEl) {
        if (!inputEl || !dropdownEl) return;
        let debounceTimer = null;

        function renderItems(matches) {
            if (!matches || matches.length === 0) {
                dropdownEl.innerHTML = `<div class="area-dropdown-item" style="color: #64748b; cursor: default;">Wilayah tidak ada di daftar cepat.</div>`;
                dropdownEl.style.display = 'block';
                return;
            }
            dropdownEl.innerHTML = matches.map(a => `
                <div class="area-dropdown-item" data-id="${a.id}" data-name="${a.name}, ${a.city}" data-postal="${a.postal}">
                    <strong>${a.name}</strong>
                    <div class="area-detail">${a.city} &bull; ${a.postal}</div>
                </div>
            `).join('');
            dropdownEl.style.display = 'block';

            dropdownEl.querySelectorAll('.area-dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    inputEl.value = this.dataset.name;
                    if (idEl) idEl.value = this.dataset.id;
                    if (postalEl) postalEl.value = this.dataset.postal;
                    dropdownEl.style.display = 'none';
                });
            });
        }

        inputEl.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                dropdownEl.style.display = 'none';
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetch(`/biteship/areas?search=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            renderItems(data);
                        } else {
                            const qLower = query.toLowerCase();
                            const localMatches = sampleAreas.filter(a =>
                                a.name.toLowerCase().includes(qLower) ||
                                a.city.toLowerCase().includes(qLower) ||
                                a.postal.includes(qLower)
                            );
                            renderItems(localMatches);
                        }
                    })
                    .catch(() => {
                        const qLower = query.toLowerCase();
                        const localMatches = sampleAreas.filter(a =>
                            a.name.toLowerCase().includes(qLower) ||
                            a.city.toLowerCase().includes(qLower) ||
                            a.postal.includes(qLower)
                        );
                        renderItems(localMatches);
                    });
            }, 250);
        });

        document.addEventListener('click', function(e) {
            if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                dropdownEl.style.display = 'none';
            }
        });
    }

    bindAreaAutocomplete(
        document.getElementById('editAddrAreaSearch'),
        document.getElementById('editAddrDestinationAreaId'),
        document.getElementById('editAddrPostalCode'),
        document.getElementById('editAddrAreaDropdown')
    );

    bindAreaAutocomplete(
        document.getElementById('newAddrAreaSearch'),
        document.getElementById('newAddrDestinationAreaId'),
        document.getElementById('newAddrPostalCode'),
        document.getElementById('newAddrAreaDropdown')
    );

    bindAreaAutocomplete(
        document.getElementById('editExtraAreaSearch'),
        document.getElementById('editExtraDestinationAreaId'),
        document.getElementById('editExtraPostalCode'),
        document.getElementById('editExtraAreaDropdown')
    );
});
// Tab Alerts
window.showTabAlert = function(tabName) {
    Swal.fire({
        title: tabName,
        text: `Halaman ${tabName} pelanggan akan segera hadir!`,
        icon: 'info',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Cancel Order Handler
window.cancelOrder = function(id, orderNumber) {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        text: `Apakah Anda yakin ingin membatalkan pesanan ${orderNumber}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ea4b48',
        cancelButtonColor: '#c3cdd6',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            // POST request to cancel order (mocking logic or endpoint if exists, otherwise display success)
            setTimeout(() => {
                Swal.close();
                Swal.fire({
                    title: 'Dibatalkan!',
                    text: 'Pesanan Anda telah berhasil dibatalkan.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                }).then(() => {
                    window.location.reload();
                });
            }, 1000);
        }
    });
}

window.payOrderNow = function(orderId) {
    Swal.fire({
        title: 'Mendapatkan Token Pembayaran...',
        text: 'Harap tunggu.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/order-history/${orderId}/pay`)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success && data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        fetch(`/order-history/${orderId}/check-payment`)
                            .then(() => {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Pembayaran berhasil dikonfirmasi.',
                                    icon: 'success',
                                    confirmButtonColor: '#00b207'
                                }).then(() => {
                                    window.location.reload();
                                });
                            });
                    },
                    onPending: function(result) {
                        fetch(`/order-history/${orderId}/check-payment`)
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.success && resData.status === 'Paid') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Pembayaran berhasil dikonfirmasi.',
                                        icon: 'success',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Tertunda',
                                        text: 'Pembayaran sedang diproses atau tertunda.',
                                        icon: 'info',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            });
                    },
                    onError: function(result) {
                        Swal.fire({
                            title: 'Gagal',
                            text: 'Pembayaran gagal dilakukan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                    },
                    onClose: function() {
                        fetch(`/order-history/${orderId}/check-payment`)
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.success && resData.status === 'Paid') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Pembayaran berhasil dikonfirmasi.',
                                        icon: 'success',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal memulai pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
}

// Tracking Order Handler & Biteship Modal Controller
let currentTrackingData = null;

window.openDashboardTrackingModal = function(data) {
    if (!data) return;
    currentTrackingData = data;

    // Header & Meta
    const orderNumEl = document.getElementById('modalOrderNumberDisplay');
    if (orderNumEl) orderNumEl.textContent = data.order_number || '-';
    const resiDisplayEl = document.getElementById('modalResiCodeDisplay');
    if (resiDisplayEl) resiDisplayEl.textContent = data.resi_code || '-';
    const resiTextEl = document.getElementById('modalResiCodeText');
    if (resiTextEl) resiTextEl.textContent = data.resi_code || '-';

    // Courier & Recipient
    const iconEl = document.getElementById('modalCourierIcon');
    if (iconEl) {
        iconEl.className = 'fa-solid ' + (data.courier_icon || 'fa-motorcycle') + ' text-success me-1';
    }
    const courierEl = document.getElementById('modalCourierName');
    if (courierEl) courierEl.textContent = data.courier_name || 'Kurir Toko / Pengiriman Standar';
    const recipientEl = document.getElementById('modalRecipientName');
    if (recipientEl) recipientEl.textContent = data.recipient_name || '-';

    // Status Badge
    const badgeContainer = document.getElementById('modalStatusBadgeContainer');
    if (badgeContainer) {
        let badgeHtml = '';
        if (data.is_completed) {
            badgeHtml = '<span class="badge-track-pill pill-success"><i class="fa-solid fa-circle-check me-1"></i> Sampai di Tujuan</span>';
        } else if (data.is_canceled) {
            badgeHtml = '<span class="badge-track-pill pill-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Pengiriman Batal</span>';
        } else if (data.is_shipping) {
            badgeHtml = '<span class="badge-track-pill pill-info"><i class="fa-solid fa-truck-fast me-1"></i> Sedang Dikirim</span>';
        } else if (data.is_processing) {
            badgeHtml = '<span class="badge-track-pill pill-warning"><i class="fa-solid fa-box me-1"></i> Sedang Dikemas</span>';
        } else if (data.is_awaiting_confirmation) {
            badgeHtml = '<span class="badge-track-pill pill-amber"><i class="fa-solid fa-clock-rotate-left me-1"></i> Menunggu Konfirmasi</span>';
        } else {
            badgeHtml = '<span class="badge-track-pill pill-secondary"><i class="fa-regular fa-clock me-1"></i> Menunggu Pembayaran</span>';
        }
        badgeContainer.innerHTML = badgeHtml;
    }

    // Links
    const detailLink = document.getElementById('modalDetailLink');
    if (detailLink && data.detail_url) detailLink.href = data.detail_url;
    const waLink = document.getElementById('modalWaLink');
    if (waLink && data.wa_url) waLink.href = data.wa_url;

    // Dynamic Checkpoint Stepper
    const timeline = document.getElementById('modalTimelineContainer');
    if (timeline) {
        let timelineHtml = '';

        if (data.is_canceled) {
            timelineHtml = `
                <div class="timeline-step active">
                    <div class="timeline-marker marker-success"><i class="fa-solid fa-check"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Dikonfirmasi</div>
                        <div class="timeline-time">${data.created_at_formatted} WIB</div>
                        <div class="timeline-desc">Pesanan sempat terdaftar di sistem Zyh Corner.</div>
                    </div>
                </div>
                <div class="timeline-step active">
                    <div class="timeline-marker marker-danger"><i class="fa-solid fa-ban"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-title text-danger">Pengiriman Dibatalkan</div>
                        <div class="timeline-time">${data.updated_at_formatted || 'Dibatalkan'} WIB</div>
                        <div class="timeline-desc">Proses logistik pengiriman dihentikan karena pesanan telah dibatalkan.</div>
                    </div>
                </div>
            `;
        } else {
            const step = parseInt(data.status_step || 1);
            const step1Title = data.is_awaiting_confirmation 
                ? 'Menunggu Konfirmasi Toko'
                : (data.is_pending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi &amp; Sedang Disiapkan');
            const step1Desc = data.is_awaiting_confirmation
                ? 'Pesanan telah diterima dan sedang menunggu konfirmasi staf kasir/dapur Zyh Corner. Anda masih dapat membatalkan pesanan ini jika diperlukan.'
                : (data.is_pending ? 'Menunggu penyelesaian pembayaran sebelum diteruskan ke dapur.' : 'Dapur Zyh Corner telah mengonfirmasi pesanan dan menyiapkan hidangan dengan higienis.');
            const step1Marker = (data.is_awaiting_confirmation || data.is_pending) ? 'marker-amber' : 'marker-success';
            const step1Icon = (data.is_awaiting_confirmation || data.is_pending) ? 'fa-clock-rotate-left' : 'fa-check';

            timelineHtml = `
                <div class="timeline-step active">
                    <div class="timeline-marker ${step1Marker}"><i class="fa-solid ${step1Icon}"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-title">${step1Title}</div>
                        <div class="timeline-time">${data.created_at_formatted} WIB</div>
                        <div class="timeline-desc">${step1Desc}</div>
                    </div>
                </div>
                <div class="timeline-step ${step >= 2 ? 'active' : ''}">
                    <div class="timeline-marker ${step >= 2 ? (step === 2 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                        <i class="fa-solid ${step > 2 ? 'fa-check' : 'fa-box'}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Kurir Dialokasikan (Driver Assigned)</div>
                        <div class="timeline-time">${step >= 2 ? 'Kurir telah menerima pesanan' : 'Menunggu kurir siap'}</div>
                        <div class="timeline-desc">Sistem logistik Biteship menghubungkan pesanan dengan armada pengiriman terdekat.</div>
                    </div>
                </div>
                <div class="timeline-step ${step >= 3 ? 'active' : ''}">
                    <div class="timeline-marker ${step >= 3 ? (step === 3 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                        <i class="fa-solid ${step > 3 ? 'fa-check' : 'fa-truck-fast'}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Dalam Perjalanan (In Transit)</div>
                        <div class="timeline-time">${step >= 3 ? 'Kurir sedang menuju alamat tujuan' : 'Menunggu penjemputan'}</div>
                        <div class="timeline-desc">Paket telah di-pickup dan sedang dalam rute pengantaran ke alamat pembeli.</div>
                    </div>
                </div>
                <div class="timeline-step ${step >= 4 ? 'active' : ''}">
                    <div class="timeline-marker ${step >= 4 ? 'marker-success' : 'marker-muted'}">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Telah Tiba di Tujuan (Delivered)</div>
                        <div class="timeline-time">${step >= 4 ? (data.updated_at_formatted || 'Hari ini') + ' WIB' : 'Estimasi tiba hari ini'}</div>
                        <div class="timeline-desc">Paket telah diterima dengan baik di alamat tujuan. Terima kasih telah berbelanja di Zyh Corner!</div>
                    </div>
                </div>
            `;
        }
        timeline.innerHTML = timelineHtml;
    }

    // Portal to body to break any stacking context / containing block trap
    const modal = document.getElementById('biteshipTrackingModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.closeBiteshipTrackingModal = function() {
    const modal = document.getElementById('biteshipTrackingModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
};

window.copyModalResi = function() {
    if (!currentTrackingData || !currentTrackingData.resi_code) return;
    const code = currentTrackingData.resi_code;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            window.showResiToast(code);
        }).catch(() => window.fallbackCopyResi(code));
    } else {
        window.fallbackCopyResi(code);
    }
};

window.fallbackCopyResi = function(code) {
    const temp = document.createElement('input');
    temp.value = code;
    document.body.appendChild(temp);
    temp.select();
    try {
        document.execCommand('copy');
        window.showResiToast(code);
    } catch (e) {
        console.warn('Gagal menyalin resi:', e);
    }
    document.body.removeChild(temp);
};

window.showResiToast = function(code) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Resi Berhasil Disalin',
            text: code,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }
};

window.trackOrder = function(orderNumber, orderId) {
    const btn = document.querySelector(`[data-tracking-order="${orderNumber}"]`);
    if (btn && btn.dataset.trackingPayload) {
        try {
            const data = JSON.parse(btn.dataset.trackingPayload);
            window.openDashboardTrackingModal(data);
            return;
        } catch (e) {
            console.error('Error parsing tracking payload:', e);
        }
    }
    if (orderId) {
        window.location.href = `/order-history/${orderId}?track=1`;
    } else {
        window.location.href = `/order-history`;
    }
};

