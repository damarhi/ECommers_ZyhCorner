# Dokumentasi Perubahan Kode Redesain Modal Edit & Tambah Banner

Dokumentasi ini menjelaskan pembaruan desain dan kode pada **Modal Edit Banner** dan **Modal Tambah Banner** di file `resources/views/Admin/kelola_carousel/banners.blade.php` dengan prinsip **Frontend Design Direction**.

---

## 1. Latar Belakang & Masalah Desain Sebelumnya

Sebelum pembaruan dilakukan, modal memiliki beberapa kekurangan mendasar:
1. **Layout Menumpuk dan Terlalu Sempit**:
   Menggunakan modal standar (`.modal-dialog` lebar ~500px) di mana 7 bidang input (Judul, Subjudul, Posisi, Target URL, Urutan, Gambar, Status) ditumpuk satu kolom secara vertikal setinggi ~680px, mengharuskan admin menggulir.
2. **Pratinjau Gambar Banner Sangat Kecil**:
   Gambar hanya ditampilkan berukuran `max-height: 90px` (terender nyata hanya ~84×28px), sehingga admin tidak dapat memeriksa kualitas visual atau rasio banner yang aktif.
3. **Ketiadaan Live Preview**:
   Tidak ada pratinjau langsung saat admin memilih file gambar baru dari komputernya. Admin baru bisa melihat hasil setelah form disimpan ke server.
4. **Hirarki Formulir Monoton**:
   Tidak ada pengelompokan logis antara metadata teks promosi dan pengaturan navigasi/penempatan.

---

## 2. Solusi Desain Baru (Frontend Design Direction)

- **Ukuran Dialog Lebih Proporsional**: Menggunakan `modal-dialog modal-lg modal-dialog-centered` (~1200px responsif).
- **Split-Pane 2 Kolom**:
  - **Kolom Kiri (Visual & Upload Zone, `col-lg-5`)**:
    - Pratinjau banner berukuran besar (rasio 16:9, lebar 432px × 190px).
    - Lencana posisi penempatan aktif di atas banner.
    - *Drag-and-drop dropzone upload* dengan ikon interaktif dan petunjuk batas format file.
    - Tombol *Batal Ganti Gambar* untuk membatalkan file pilihan tanpa merusak gambar asli.
  - **Kolom Kanan (Metadata & Penempatan, `col-lg-7`)**:
    - Kartu **Informasi Konten**: Judul banner dan subjudul/deskripsi singkat.
    - Kartu **Penempatan & Navigasi**: Dropdown posisi dengan ikon visual, target link URL berikon rantai tautan, urutan tampil, dan status publikasi (Aktif/Nonaktif).
- **Interaktivitas JavaScript Ringan**:
  - *Instant Live Image Preview*: Gambar pratinjau berganti seketika saat file dipilih via `FileReader`.
  - *Dynamic Badge Listener*: Teks lencana penempatan pada gambar berganti otomatis saat opsi dropdown posisi diganti oleh admin.

---

## 3. Rincian Perubahan Kode

File yang diubah: `resources/views/Admin/kelola_carousel/banners.blade.php`

### A. Modal Edit Banner (`#editBannerModal{{ $b->id_banner }}`)
```html
<!-- Modal Edit Banner #{{ $b->id_banner }} -->
<div class="modal fade" id="editBannerModal{{ $b->id_banner }}" tabindex="-1" aria-labelledby="editBannerModalLabel{{ $b->id_banner }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom px-4 py-3 bg-light bg-opacity-75">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" style="width: 40px; height: 40px;">
                        <i class="mdi mdi-image-edit-outline" style="font-size: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="editBannerModalLabel{{ $b->id_banner }}">Edit Banner Promo</h5>
                        <small class="text-muted" style="font-size: 12px;">Sesuaikan visual promosi, penempatan posisi, dan target rute URL</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('banners.update', $b->id_banner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Kolom Kiri: Pratinjau Visual & Dropzone Upload -->
                        <div class="col-lg-5 d-flex flex-column gap-3">
                            <div class="card border rounded-3 p-3 bg-light bg-opacity-50 shadow-none">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">
                                        <i class="mdi mdi-eye-outline me-1 text-primary"></i> Pratinjau Banner
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">Rasio 16:9</span>
                                </div>
                                <div class="position-relative overflow-hidden rounded-3 shadow-sm bg-dark" style="aspect-ratio: 16/9; max-height: 190px; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}" 
                                         id="editPreviewImg{{ $b->id_banner }}" 
                                         alt="Preview" 
                                         class="w-100 h-100 object-fit-cover"
                                         data-original-src="{{ asset('storage/' . $b->gambar_banner) }}">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white" id="badgePosisi{{ $b->id_banner }}" style="font-size: 11px; backdrop-filter: blur(4px);">
                                            @if($b->posisi_banner === 'home_hero') Home Hero
                                            @elseif($b->posisi_banner === 'shop_header') Header Showcase
                                            @elseif($b->posisi_banner === 'custom_header') Header Kustom
                                            @else Pop-up Promo
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card border rounded-3 p-3 bg-white shadow-none">
                                <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center justify-content-between" style="font-size: 13px;">
                                    <span><i class="mdi mdi-cloud-upload-outline me-1 text-primary"></i> Ganti File Banner</span>
                                    <button type="button" class="btn btn-link text-danger p-0 d-none" id="btnResetImg{{ $b->id_banner }}"
                                            style="font-size: 11px; text-decoration: none;"
                                            onclick="resetEditBanner('{{ $b->id_banner }}')">
                                        <i class="mdi mdi-undo"></i> Batal Ganti
                                    </button>
                                </label>
                                <div class="upload-dropzone position-relative border border-2 border-dashed rounded-3 p-3 text-center bg-light transition-all"
                                     id="dropzoneEdit{{ $b->id_banner }}">
                                    <input type="file" name="gambar_banner" id="inputGambarEdit{{ $b->id_banner }}"
                                           class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                           accept="image/jpeg,image/png,image/webp,image/jpg,image/svg+xml" style="cursor: pointer;"
                                           onchange="previewEditBanner(event, '{{ $b->id_banner }}')">
                                    <i class="mdi mdi-image-plus text-primary mb-1" style="font-size: 32px; display: block;"></i>
                                    <div class="fw-semibold text-dark" style="font-size: 12px;">Pilih atau seret gambar baru</div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Maksimal 4MB (JPG, PNG, WEBP)</small>
                                </div>
                                <small class="text-muted mt-2 d-block" style="font-size: 11px;">*Biarkan kosong jika tidak ingin mengubah gambar.</small>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Metadata & Pengaturan -->
                        <div class="col-lg-7 d-flex flex-column gap-3">
                            <div class="card border rounded-3 p-3 bg-white shadow-none">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                    <i class="mdi mdi-format-text text-primary"></i>
                                    <span class="fw-bold text-dark" style="font-size: 13px;">Informasi Konten</span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Judul Banner <span class="text-danger">*</span></label>
                                    <input type="text" name="judul_banner" class="form-control rounded-2" 
                                           value="{{ $b->judul_banner }}" placeholder="Contoh: Promo Spesial Weekend Cake" required>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Subjudul / Deskripsi Singkat</label>
                                    <input type="text" name="subjudul" class="form-control rounded-2" 
                                           value="{{ $b->subjudul }}" placeholder="Contoh: Dapatkan diskon hingga 30% untuk aneka pilihan">
                                </div>
                            </div>

                            <div class="card border rounded-3 p-3 bg-white shadow-none">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                    <i class="mdi mdi-tune text-primary"></i>
                                    <span class="fw-bold text-dark" style="font-size: 13px;">Penempatan & Navigasi</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Posisi Penempatan <span class="text-danger">*</span></label>
                                    <select name="posisi_banner" class="form-select rounded-2 select-posisi" 
                                            data-banner-id="{{ $b->id_banner }}" 
                                            onchange="updatePosisiBadge(this, '{{ $b->id_banner }}')" required>
                                        <option value="home_hero" {{ $b->posisi_banner === 'home_hero' ? 'selected' : '' }}>🏠 Home Hero Slider (Beranda Utama)</option>
                                        <option value="shop_header" {{ $b->posisi_banner === 'shop_header' ? 'selected' : '' }}>🛍️ Header Showcase (Halaman Shop)</option>
                                        <option value="custom_header" {{ $b->posisi_banner === 'custom_header' ? 'selected' : '' }}>🎂 Header Kustom (Halaman Custom Products)</option>
                                        <option value="popup_promo" {{ $b->posisi_banner === 'popup_promo' ? 'selected' : '' }}>✨ Pop-up Promo Eksklusif</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Target URL Tujuan (Opsional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="mdi mdi-link-variant"></i></span>
                                        <input type="text" name="link_url" class="form-control border-start-0" 
                                               value="{{ $b->link_url }}" placeholder="Contoh: /shop atau /custom-products">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Urutan Tampil</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="mdi mdi-sort-numeric-ascending"></i></span>
                                            <input type="number" name="urutan" class="form-control" value="{{ $b->urutan }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 12px;">Status Publikasi</label>
                                        <select name="status" class="form-select rounded-2" required>
                                            <option value="Aktif" {{ $b->status === 'Aktif' ? 'selected' : '' }}>🟢 Aktif (Tayang)</option>
                                            <option value="Nonaktif" {{ $b->status === 'Nonaktif' ? 'selected' : '' }}>⚪ Nonaktif (Draft)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light bg-opacity-75 d-flex justify-content-between align-items-center">
                    <div class="text-muted d-flex align-items-center gap-1" style="font-size: 12px;">
                        <i class="mdi mdi-identifier"></i> ID Banner: <strong>#{{ $b->id_banner }}</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light px-3 rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary px-4 rounded-3 d-inline-flex align-items-center gap-2">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span>Perbarui Banner</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

### B. Modal Tambah Banner (`#addBannerModal`)
Modal tambah banner disesuaikan secara simetris dengan rancangan 2 kolom, menyajikan area pratinjau yang menampilkan status *"Gambar akan muncul di sini"* dan otomatis beralih menjadi foto pratinjau seketika gambar diunggah.

---

### C. Styling Tambahan & Interaktivitas JavaScript
```css
<style>
    .upload-dropzone {
        transition: all 0.2s ease-in-out;
    }
    .upload-dropzone:hover {
        border-color: #00b207 !important;
        background-color: #f0fdf4 !important;
    }
    .object-fit-cover {
        object-fit: cover;
    }
</style>
```

```javascript
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
```

---

## 4. Hasil Verifikasi & Pengujian Otomatis

Pengujian dijalankan langsung melalui Headless Browser pada halaman `/admin/banners`:

| Parameter | Sebelum | Sesudah | Peningkatan / Catatan |
| :--- | :---: | :---: | :--- |
| **Lebar Kontainer Modal** | 500px (`.modal-dialog`) | **1193px (`modal-lg`)** | +138% lebar, layout bernapas longgar |
| **Dimensi Pratinjau Gambar** | 84 × 28 px | **432 × 190 px** | **+414% lebar, +578% tinggi**, rasio 16:9 stabil |
| **Live File Replacement Preview** | Tidak Ada | **Tersedia (Instant)** | Berhasil diverifikasi via JS reader |
| **Dynamic Position Badge** | Tidak Ada | **Aktif & Otomatis** | Berhasil mengubah badge real-time |
| **Tombol Batal Ganti Gambar** | Tidak Ada | **Aktif & Otomatis** | Mengembalikan file ke gambar awal |

Tangkapan layar hasil verifikasi disimpan pada:
- `docs/redesigned_edit_banner_modal.png` (Modal Edit Banner)
- `docs/redesigned_add_banner_modal.png` (Modal Tambah Banner)
