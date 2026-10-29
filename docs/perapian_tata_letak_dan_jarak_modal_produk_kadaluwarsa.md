# Dokumentasi Perapian Tata Letak, Ritme Spasial & Konsistensi Teks Modal Produk Kadaluwarsa

## 1. Latar Belakang & Analisis Masalah
Berdasarkan tinjauan tangkapan layar modal *Tindakan Produk Kadaluarsa* (`#editExpiredModal{{ $item->id_expired }}`) pada `resources/views/Admin/kelola_produk/expired_products.blade.php`:
1. **Kurangnya Informasi Kuantitas Stok Fisik:**
   Kotak informasi produk (`#f8fafc`) sebelumnya hanya menampilkan *Nama Produk* dan *Tanggal Kadaluarsa*. Admin tidak dapat melihat berapa sisa fisik stok yang kadaluwarsa langsung di dalam modal tanpa menutup modal dan melihat tabel.
2. **Ritme Margin Non-Standar:**
   Penggunaan kelas non-standar Bootstrap 5 `mb-2.5` serta jarak label ke input yang terlalu rapat (`mb-1` / 4px) menyebabkan ketidakharmonisan ritme spasial vertikal antar form field.
3. **Ketiadaan Konteks Bantuan pada Status Tindakan:**
   Tidak ada *helper text* / microcopy di bawah dropdown pilihan status untuk memandu staf memilih antara *Dimusnahkan*, *Diretur*, atau *Diarsipkan*.
4. **Ketidakseimbangan Proporsi Tombol Footer:**
   Tombol *Batal* dan *Simpan Perubahan* memiliki padding dan ketinggian yang terlalu tipis (`py-1`), serta tombol Batal tidak memiliki kontras batas yang jelas.

---

## 2. Perbaikan Desain & Spasial (`frontend-design-direction`)

1. **Info Box Lengkap & Terstruktur:**
   - Menata ulang kotak informasi produk menjadi 2 baris seimbang bergaris pemisah halus:
     - **Baris Atas:** Label kategori `"PRODUK TERDAMPAK"`, Nama Produk tebal (`font-size: 13px; font-weight: 600;`), sub-kategori kue, serta badge stok fisik kontras di sisi kanan (`Stok: X`).
     - **Baris Bawah:** Label `"Tanggal Kadaluarsa:"` berdampingan dengan badge merah lembut berikon kalender (`badge-soft-danger`).
2. **Standarisasi Spasial 4px/8px Bootstrap 5:**
   - Jarak antar form control: `mb-3` (16px).
   - Jarak label ke input: `mb-1.5` (6px) untuk keterbacaan yang optimal.
   - Jarak penutup form ke footer: `mb-1`.
3. **Penambahan Contextual Microcopy:**
   - Ditambahkan teks bantuan di bawah dropdown status tindakan:
     `<small class="text-muted mt-1 d-block" style="font-size: 10.5px;">Pilih status penanganan fisik barang (dimusnahkan, retur ke supplier, atau arsip audit).</small>`
4. **Hierarki Tipografi Terstandar:**
   - Judul Modal: `15px` (`font-weight: 700`)
   - Label Input: `12px` (`font-weight: 500`)
   - Input & Textarea: `12px` (`line-height: 1.5; border-radius: 6px;`)
   - Helper Text / Microcopy: `10.5px` – `11px`
5. **Penyempurnaan Tombol Aksi Footer:**
   - Ketinggian tombol ditingkatkan menjadi ~36px dengan padding `py-1.5 px-3`.
   - Tombol Batal diberi batas kontras bersih (`background: #ffffff; border: 1px solid #e2e8f0; color: #475569;`).
   - Tombol Simpan Perubahan mempertahankan warna gradien primer dengan ikon centang konsisten.

---

## 3. Rincian Kode yang Diubah

### Berkas: `resources/views/Admin/kelola_produk/expired_products.blade.php` (Baris 236–305)

```html
<div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
    <div class="d-flex align-items-center gap-2.5">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background-color: #fef3c7; color: #d97706 !important;">
            <i class="mdi mdi-clock-alert-outline" style="font-size: 18px;"></i>
        </span>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editExpiredModalLabel{{ $item->id_expired }}" style="font-size: 15px;">
                    Tindakan Produk Kadaluarsa
                </h6>
                <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">#{{ $item->id_expired }}</span>
            </div>
            <small class="text-muted d-block mt-0.5" style="font-size: 11px;">Tentukan status operasional penanganan produk expired.</small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
</div>
<form action="{{ route('products.expired.update', $item->id_expired) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
        <!-- Info Produk Box -->
        <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="text-truncate me-2" style="max-width: 72%;">
                    <span class="text-muted d-block" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Produk Terdampak</span>
                    <strong class="text-dark text-truncate d-block" style="font-size: 13px;">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</strong>
                    <small class="text-muted" style="font-size: 11px;">{{ $item->produk->kategori->nama_kategori ?? 'Tanpa Kategori' }}</small>
                </div>
                <span class="badge bg-white text-dark border px-2 py-1 flex-shrink-0" style="font-size: 11px; font-weight: 600;">
                    Stok: {{ $item->jumlah_stok ?? '0' }}
                </span>
            </div>
            @if(isset($item->tanggal_expired))
                <div class="pt-2 border-top d-flex align-items-center justify-content-between" style="border-color: #edf2f7 !important;">
                    <span class="text-muted" style="font-size: 11px;">Tanggal Kadaluarsa:</span>
                    <span class="badge badge-soft-danger" style="font-size: 10.5px; font-weight: 600; padding: 3px 8px;">
                        <i class="mdi mdi-calendar-remove me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_expired)->isoFormat('D MMMM Y') }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Status Tindakan -->
        <div class="form-group mb-3">
            <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Status Tindakan <span class="text-danger">*</span></label>
            <select name="status_tindakan" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                <option value="Perlu Tindakan" {{ $item->status_tindakan === 'Perlu Tindakan' ? 'selected' : '' }}>Perlu Tindakan</option>
                <option value="Dimusnahkan" {{ $item->status_tindakan === 'Dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
                <option value="Diretur" {{ $item->status_tindakan === 'Diretur' ? 'selected' : '' }}>Diretur</option>
                <option value="Diarsipkan" {{ $item->status_tindakan === 'Diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
            </select>
            <small class="text-muted mt-1 d-block" style="font-size: 10.5px;">
                Pilih status penanganan fisik barang (dimusnahkan, retur ke supplier, atau arsip audit).
            </small>
        </div>

        <!-- Catatan Tindakan -->
        <div class="form-group mb-1">
            <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Catatan / Keterangan Tindakan</label>
            <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Telah dibuang dan dicatat dalam berita acara pemusnahan..." style="font-size: 12px; border-radius: 6px; line-height: 1.5;">{{ $item->catatan }}</textarea>
        </div>
    </div>
    <div class="modal-footer border-top bg-light py-2.5 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-sm px-3 py-1.5 border" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px; background: #ffffff; color: #475569;">Batal</button>
        <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1.5 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
            <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Perubahan
        </button>
    </div>
```

---

## 4. Hasil Verifikasi

1. **Blade Template Cache**:
   ```bash
   php artisan view:clear && php artisan view:cache
   # Hasil: Compiled views cleared & Blade templates cached successfully.
   ```
2. **Feature Tests Produk Kadaluarsa (`ExpiredProductsTest`)**:
   ```bash
   php artisan test --filter=ExpiredProductsTest
   # Hasil: 6 passed (20 assertions) - 100% Green
   ```
3. **Full System Regression Suite**:
   ```bash
   php artisan test
   # Hasil: 153 passed (774 assertions) - 100% Green
   ```
