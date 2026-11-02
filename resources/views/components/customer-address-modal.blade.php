@props(['user'])

<!-- Modal: Edit Address (Complete 1:1 with Geocoding Map & Biteship Area) -->
<div class="edit-modal-overlay" id="modalEditAddress" style="display: none;">
    <div class="edit-modal edit-modal-wide">
        <div class="edit-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="modal-title-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                <div>
                    <h4 class="edit-modal-title" id="editModalHeaderTitle">Edit Alamat Pengiriman</h4>
                    <p class="edit-modal-subtitle">Perbarui rincian penerima, alamat jalan, dan titik GPS pengantaran</p>
                </div>
            </div>
            <button type="button" class="edit-modal-close" onclick="closeEditAddress()">&times;</button>
        </div>

        <form id="formEditAddress" onsubmit="saveAddress(event)">
            <input type="hidden" id="editAddrId" value="">
            
            <!-- Mirrored compatibility fields for legacy tests / scripts -->
            <input type="hidden" id="inputBillingAddress" value="{{ $user->alamat ?? '' }}">
            <input type="hidden" id="inputBillingEmail" value="{{ $user->email }}">
            <input type="hidden" id="inputBillingPhone" value="{{ $user->no_hp }}">

            <div class="modal-scrollable-body">
                <!-- Group 1: Label Alamat & Identitas Penerima -->
                <div class="modal-form-section">
                    <div class="modal-section-heading">
                        <i class="fa-solid fa-user-tag"></i> Identitas & Label Alamat
                    </div>
                    
                    <div class="form-group-modal">
                        <label for="editAddrLabel">
                            <i class="fa-solid fa-tag"></i> Label Alamat
                        </label>
                        <div class="label-input-with-chips">
                            <input type="text" id="editAddrLabel" class="form-control-modal" placeholder="Contoh: Rumah, Kantor, Toko" value="Alamat Rumah" required>
                            <div class="quick-chips-group">
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Rumah')">Rumah</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Kantor')">Kantor</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Toko')">Toko</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Apartemen')">Apartemen</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group-modal">
                            <label for="editAddrRecipientName">
                                <i class="fa-solid fa-user"></i> Nama Penerima
                            </label>
                            <input type="text" id="editAddrRecipientName" class="form-control-modal" placeholder="Nama penerima pesanan" value="{{ $user->nama }}" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="editAddrRecipientPhone">
                                <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
                            </label>
                            <input type="tel" id="editAddrRecipientPhone" class="form-control-modal" placeholder="08xxxxxxxxxx" value="{{ $user->no_hp }}" required>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Wilayah Biteship & Kode Pos -->
                <div class="modal-form-section">
                    <div class="modal-section-heading">
                        <i class="fa-solid fa-location-crosshairs"></i> Wilayah & Detail Alamat
                    </div>

                    <div class="form-row-2">
                        <div class="form-group-modal area-search-wrapper">
                            <label for="editAddrAreaSearch">
                                <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
                            </label>
                            <input type="text" id="editAddrAreaSearch" class="form-control-modal" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
                            <input type="hidden" id="editAddrDestinationAreaId" value="">
                            <div id="editAddrAreaDropdown" class="area-dropdown-results"></div>
                        </div>
                        <div class="form-group-modal">
                            <label for="editAddrPostalCode">
                                <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
                            </label>
                            <input type="text" id="editAddrPostalCode" class="form-control-modal" maxlength="5" placeholder="Contoh: 28282" inputmode="numeric">
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label for="editAddrStreetAddress">
                            <i class="fa-solid fa-location-dot"></i> Detail Alamat Lengkap
                        </label>
                        <textarea id="editAddrStreetAddress" class="form-control-modal" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required>{{ $user->alamat ?? '' }}</textarea>
                    </div>

                    <div class="form-group-modal" style="margin-bottom: 0;">
                        <label for="editAddrNotes">
                            <i class="fa-regular fa-clipboard"></i> Catatan Pengiriman / Patokan <span class="label-optional">(Opsional)</span>
                        </label>
                        <textarea id="editAddrNotes" class="form-control-modal" rows="2" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam samping masjid"></textarea>
                    </div>
                </div>

                <!-- Group 3: Titik Lokasi Peta GPS Leaflet -->
                <div class="modal-form-section">
                    <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                        <button type="button" class="btn-locate-me" onclick="locateUserPosition('edit')">
                            <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                        </button>
                    </div>

                    <!-- Map Geocoding Search Bar -->
                    <div class="map-search-container" id="editAddrMapSearchContainer">
                        <div class="map-search-bar">
                            <div class="map-search-input-wrap">
                                <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                                <input type="text" id="editAddrMapSearchInput" class="map-search-input" 
                                       placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                                       autocomplete="off"
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('edit'); }"
                                       oninput="toggleClearSearchBtn('edit')">
                                <button type="button" class="btn-clear-map-search" id="editAddrClearSearchBtn" onclick="clearMapSearch('edit')" style="display: none;" title="Hapus pencarian">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-search-map" onclick="searchLocationOnMap('edit')">
                                Cari
                            </button>
                        </div>
                        <div id="editAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
                    </div>

                    <div class="modal-map-wrapper">
                        <div id="modalEditMap" class="modal-map-canvas"></div>
                        <div class="modal-map-bar">
                            <i class="fa-solid fa-location-crosshairs"></i>
                            <span id="modalEditCoordsDisplay">Klik peta atau seret pin marker untuk menandai titik tepat Anda</span>
                        </div>
                    </div>
                    <input type="hidden" id="editAddrLatitude" value="">
                    <input type="hidden" id="editAddrLongitude" value="">
                </div>

                <!-- Group 4: Pengaturan Alamat Utama -->
                <div class="form-group-modal" style="margin-bottom: 8px;">
                    <label class="default-address-toggle">
                        <input type="checkbox" id="editAddrIsDefault" value="1">
                        <span class="toggle-text">
                            <strong>Jadikan sebagai alamat utama</strong>
                            <small>Alamat ini akan otomatis terpilih secara default pada setiap checkout pesanan Anda.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="modal-footer-actions">
                <button type="button" class="btn-cancel-modal" onclick="closeEditAddress()">Batal</button>
                <button type="submit" class="btn-save-modal" id="btnSaveEditAddress">
                    <i class="fa-solid fa-check"></i> Simpan Perubahan Alamat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add New Address (Complete 1:1 with Geocoding Map & Biteship Area) -->
<div class="edit-modal-overlay" id="modalAddAddress" style="display: none;">
    <div class="edit-modal edit-modal-wide">
        <div class="edit-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="modal-title-icon"><i class="fa-solid fa-plus-circle"></i></span>
                <div>
                    <h4 class="edit-modal-title">Tambah Alamat Pengiriman Baru</h4>
                    <p class="edit-modal-subtitle">Simpan alamat baru ke buku alamat akun Anda untuk digunakan saat checkout</p>
                </div>
            </div>
            <button type="button" class="edit-modal-close" onclick="closeAddAddressModal()">&times;</button>
        </div>

        <form id="formAddAddress" onsubmit="submitNewAddress(event)">
            <div class="modal-scrollable-body">
                <!-- Group 1: Label Alamat & Identitas Penerima -->
                <div class="modal-form-section">
                    <div class="modal-section-heading">
                        <i class="fa-solid fa-user-tag"></i> Identitas & Label Alamat
                    </div>
                    
                    <div class="form-group-modal">
                        <label for="newAddrLabel">
                            <i class="fa-solid fa-tag"></i> Label Alamat
                        </label>
                        <div class="label-input-with-chips">
                            <input type="text" id="newAddrLabel" class="form-control-modal" placeholder="Contoh: Rumah, Kantor, Toko" value="Alamat Rumah" required>
                            <div class="quick-chips-group">
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Rumah')">Rumah</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Kantor')">Kantor</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Toko')">Toko</button>
                                <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Apartemen')">Apartemen</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group-modal">
                            <label for="newAddrRecipientName">
                                <i class="fa-solid fa-user"></i> Nama Penerima
                            </label>
                            <input type="text" id="newAddrRecipientName" class="form-control-modal" placeholder="Nama penerima pesanan" value="{{ $user->nama }}" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="newAddrRecipientPhone">
                                <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
                            </label>
                            <input type="tel" id="newAddrRecipientPhone" class="form-control-modal" placeholder="08xxxxxxxxxx" value="{{ $user->no_hp }}" required>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Wilayah Biteship & Detail Alamat -->
                <div class="modal-form-section">
                    <div class="modal-section-heading">
                        <i class="fa-solid fa-location-crosshairs"></i> Wilayah & Detail Alamat
                    </div>

                    <div class="form-row-2">
                        <div class="form-group-modal area-search-wrapper">
                            <label for="newAddrAreaSearch">
                                <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
                            </label>
                            <input type="text" id="newAddrAreaSearch" class="form-control-modal" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
                            <input type="hidden" id="newAddrDestinationAreaId" value="">
                            <div id="newAddrAreaDropdown" class="area-dropdown-results"></div>
                        </div>
                        <div class="form-group-modal">
                            <label for="newAddrPostalCode">
                                <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
                            </label>
                            <input type="text" id="newAddrPostalCode" class="form-control-modal" maxlength="5" placeholder="Contoh: 28282" inputmode="numeric">
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label for="newAddrStreetAddress">
                            <i class="fa-solid fa-location-dot"></i> Detail Alamat Lengkap
                        </label>
                        <textarea id="newAddrStreetAddress" class="form-control-modal" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required></textarea>
                    </div>

                    <div class="form-group-modal" style="margin-bottom: 0;">
                        <label for="newAddrNotes">
                            <i class="fa-regular fa-clipboard"></i> Catatan Pengiriman / Patokan <span class="label-optional">(Opsional)</span>
                        </label>
                        <textarea id="newAddrNotes" class="form-control-modal" rows="2" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam samping masjid"></textarea>
                    </div>
                </div>

                <!-- Group 3: Titik Lokasi Peta GPS Leaflet -->
                <div class="modal-form-section">
                    <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                        <button type="button" class="btn-locate-me" onclick="locateUserPosition('add')">
                            <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                        </button>
                    </div>

                    <!-- Map Geocoding Search Bar -->
                    <div class="map-search-container" id="newAddrMapSearchContainer">
                        <div class="map-search-bar">
                            <div class="map-search-input-wrap">
                                <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                                <input type="text" id="newAddrMapSearchInput" class="map-search-input" 
                                       placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                                       autocomplete="off"
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('add'); }"
                                       oninput="toggleClearSearchBtn('add')">
                                <button type="button" class="btn-clear-map-search" id="newAddrClearSearchBtn" onclick="clearMapSearch('add')" style="display: none;" title="Hapus pencarian">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-search-map" onclick="searchLocationOnMap('add')">
                                Cari
                            </button>
                        </div>
                        <div id="newAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
                    </div>

                    <div class="modal-map-wrapper">
                        <div id="modalAddMap" class="modal-map-canvas"></div>
                        <div class="modal-map-bar">
                            <i class="fa-solid fa-location-crosshairs"></i>
                            <span id="modalAddCoordsDisplay">Klik peta atau seret pin marker untuk menandai titik tepat Anda</span>
                        </div>
                    </div>
                    <input type="hidden" id="newAddrLatitude" value="">
                    <input type="hidden" id="newAddrLongitude" value="">
                </div>

                <!-- Group 4: Pengaturan Alamat Utama -->
                <div class="form-group-modal" style="margin-bottom: 8px;">
                    <label class="default-address-toggle">
                        <input type="checkbox" id="newAddrIsDefault" value="1">
                        <span class="toggle-text">
                            <strong>Jadikan sebagai alamat utama</strong>
                            <small>Alamat ini akan otomatis terpilih secara default pada setiap checkout pesanan Anda.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="modal-footer-actions">
                <button type="button" class="btn-cancel-modal" onclick="closeAddAddressModal()">Batal</button>
                <button type="submit" class="btn-save-modal" id="btnSaveAddAddress">
                    <i class="fa-solid fa-check"></i> Simpan Alamat Baru
                </button>
            </div>
        </form>
    </div>
</div>
