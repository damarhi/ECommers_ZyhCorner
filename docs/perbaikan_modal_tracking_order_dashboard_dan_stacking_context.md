# Dokumentasi Perbaikan Modal Tracking Order, Eliminasi Stacking Context Trap, dan In-Place Dashboard Tracking

## 1. Ringkasan Eksekutif & Akar Masalah

Berdasarkan pengujian tampilan visual pada resolusi $1568 \times 708\text{ px}$ dan audit pohon DOM (Document Object Model), modal pelacakan paket (*Biteship Tracking Modal*) sebelumnya mengalami cacat tata letak berupa pergeseran ke sisi kiri layar ($x: 60\text{px} - 650\text{px}$) serta mengekspos footer gelap (*dark footer*) di sisi kanan layar. 

Pemeriksaan kode sumber mendalam mengidentifikasi **3 akar masalah teknis**:

1. **Tag HTML Unclosed pada Timeline Modal (`order_detail.blade.php`)**:
   - Pada baris 2433, elemen `<div class="timeline-step active">` (Step 1) membungkus penanda dan konten, namun hanya `.timeline-content` yang ditutup pada baris 2441. Tag penutup pembungkus langkah itu sendiri (`</div>`) terlewat.
   - Akibatnya, seluruh langkah berikutnya (Step 2 hingga Step 5) menjadi *nested children* di dalam Step 1. Rantai penutup tag di bagian bawah modal menutup `.biteship-modal-backdrop` dan `.biteship-modal-dialog` lebih cepat, sehingga `.biteship-modal-footer` terlempar ke luar struktur dialog.
2. **Tag Penutup Ekstra Liar (`dashboard.blade.php`)**:
   - Pada baris 590–591 terdapat dua tag `</div></div>` liar tanpa pembuka yang merusak hierarki kontainer layout utama `layout_pelanggan.blade.php`, memutus *wrapper* halaman sebelum footer dan merusak *stacking context*.
3. **CSS Containing Block / Stacking Context Trap**:
   - Sesuai spesifikasi W3C CSS Transforms, elemen dengan `position: fixed` tidak lagi mengacu pada *viewport* browser jika leluhurnya memiliki properti `transform`, `filter`, `perspective`, atau `contain`. Kontainer kolom dashboard PurpleAdmin mengurung `.biteship-modal-backdrop` di dalam lebar kolom sempit kartu riwayat pesanan.
4. **Keterbatasan UX Dashboard**:
   - Tombol `Tracking Order` pada dashboard sebelumnya hanya merupakan tautan biasa yang me-redirect pengguna ke `/order-detail/{id}?track=1`, memaksa reload halaman penuh alih-alih membuka modal pelacakan instan (*in-place modal*) seperti pada halaman riwayat pesanan (`order_history.blade.php`).

---

## 2. Berkas-Berkas yang Diubah

| No | Berkas | Perubahan Utama |
|---|---|---|
| 1 | `resources/views/Pelanggan/order_detail.blade.php` | Menutup tag `timeline-step active`, memperkuat CSS backdrop dan dialog dengan `!important`, serta portalling modal ke `document.body`. |
| 2 | `resources/views/Pelanggan/dashboard.blade.php` | Menghapus 2 tag penutup liar `</div>`, menyusun `$trackingPayload`, mengubah tombol tracking menjadi *in-place modal trigger*, dan menambahkan markup `#biteshipTrackingModal`. |
| 3 | `resources/js/dashboard.js` | Mengimplementasikan fungsi kendali modal tracking (`openDashboardTrackingModal`, `closeBiteshipTrackingModal`, `copyModalResi`, `fallbackCopyResi`, `showResiToast`, portalling ke body, dan sinkronisasi `window.trackOrder`). |
| 4 | `resources/css/pelanggan_pages.css` | Memperkuat aturan CSS `.biteship-modal-backdrop` (`position: fixed !important; inset: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 999999 !important;`) dan `.biteship-modal-dialog` (`margin: auto !important; max-width: 580px !important;`). |
| 5 | `resources/views/Pelanggan/order_history.blade.php` | Menambahkan proteksi portalling ke `document.body` pada saat modal dibuka. |

---

## 3. Detail Perubahan Kode

### A. Perbaikan Tag Unclosed pada `resources/views/Pelanggan/order_detail.blade.php`

**Sebelum (Baris 2433–2442):**
```blade
<!-- Step 1: Pesanan Dikonfirmasi & Sedang Disiapkan (Paling Atas) -->
<div class="timeline-step active">
    <div class="timeline-marker marker-success">
        <i class="fa-solid fa-check"></i>
    </div>
    <div class="timeline-content">
        <div class="timeline-title">{{ $isAwaitingConfirmation ? 'Menunggu Konfirmasi Toko' : ($isPending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi & Sedang Disiapkan') }}</div>
        <div class="timeline-time">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
        <div class="timeline-desc">{{ ... }}</div>
</div>
<!-- Step 2 menjadi anak di dalam Step 1 karena tag penutup kurang -->
<div class="timeline-step {{ $statusStep >= 2 ? 'active' : '' }}">
```

**Sesudah (Baris 2433–2443):**
```blade
<!-- Step 1: Pesanan Dikonfirmasi & Sedang Disiapkan (Paling Atas) -->
<div class="timeline-step active">
    <div class="timeline-marker marker-success">
        <i class="fa-solid fa-check"></i>
    </div>
    <div class="timeline-content">
        <div class="timeline-title">{{ $isAwaitingConfirmation ? 'Menunggu Konfirmasi Toko' : ($isPending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi & Sedang Disiapkan') }}</div>
        <div class="timeline-time">{{ $order->created_at->format('d M Y, H:i') }} WIB</div>
        <div class="timeline-desc">{{ ... }}</div>
    </div>
</div>
<!-- Step 2 berdiri sejajar sebagai sibling -->
<div class="timeline-step {{ $statusStep >= 2 ? 'active' : '' }}">
```

---

### B. Pembersihan Tag Penutup Liar pada `resources/views/Pelanggan/dashboard.blade.php`

**Sebelum (Baris 588–592):**
```blade
        </div>
    </div>
</div>

    </div>
</div>

<script>
    window.dashboardConfig = { ... };
```

**Sesudah:**
Dua tag `</div>\n</div>` yang tidak memiliki pasangan pembuka telah dipotong bersih (`CUT 590.=591`). Keseimbangan tag diuji menggunakan skrip Python AST parser dan menghasilkan `Total unclosed divs remaining: 0`.

---

### C. Penambahan In-Place Biteship Tracking Modal pada `resources/views/Pelanggan/dashboard.blade.php`

Pada iterasi riwayat pesanan terbaru di kartu dashboard (`@forelse($recentOrders as $order)`), ditambahkan konstruksi `$trackingPayload` terstruktur yang siap dipakai:

```blade
@php
    $trackingPayload = [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'resi_code' => $order->resi_code,
        'courier_name' => $order->formatted_courier_name,
        'courier_icon' => $order->courier_icon,
        'recipient_name' => $order->customer_name ?? $user->nama,
        'is_completed' => (bool)$order->is_completed,
        'is_canceled' => (bool)$order->is_canceled,
        'is_shipping' => (bool)$order->is_shipping,
        'is_processing' => (bool)$order->is_processing,
        'is_awaiting_confirmation' => (bool)$order->is_awaiting_confirmation,
        'is_pending' => (bool)$order->is_pending,
        'status_step' => (int)$order->status_step,
        'created_at_formatted' => $order->created_at->format('d M Y, H:i'),
        'updated_at_formatted' => $order->updated_at ? $order->updated_at->format('d M Y, H:i') : null,
        'detail_url' => route('order.detail', $order->id),
        'wa_url' => 'https://wa.me/' . ($storeSettings['wa_number'] ?? '6281298765432') . '?text=' . urlencode('Halo Zyh Corner, saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $order->resi_code . ')'),
    ];
@endphp
```

Tombol `Tracking Order` kini mendukung *Progressive Enhancement*:
```blade
<a href="{{ route('order.detail', $order->id) }}?track=1"
    onclick="event.preventDefault(); openDashboardTrackingModal({{ json_encode($trackingPayload) }});"
    data-tracking-order="{{ $order->order_number }}"
    data-tracking-payload="{{ json_encode($trackingPayload) }}"
    class="btn-tracking-solid" style="text-decoration: none;"
    title="Lacak paket dengan Biteship">
    <i class="fa-solid fa-location-crosshairs me-1"></i> Tracking Order
</a>
```
- Jika pengguna mengklik secara normal di browser, modal pelacakan langsung terbuka seketika tanpa refresh halaman (*zero latency*).
- Jika pengguna membuka tautan di tab baru (*middle click* / *right click*), sistem mengarahkannya ke halaman detail pesanan dengan parameter `?track=1` yang langsung memunculkan modal.

Di bagian bawah `dashboard.blade.php`, ditambahkan komponen modal `#biteshipTrackingModal` yang memuat:
1. Header modal dengan badge *Powered by Biteship*, judul, nomor resi, dan tombol *close* ($28 \times 28\text{px}$).
2. *Order & Courier Info Strip* (Kurir pengiriman, resi dengan tombol salin interaktif, penerima, dan status pengiriman).
3. Kontainer *Checkpoint Timeline* dinamis.
4. Footer dengan tombol Tutup, tautan Rincian Pesanan, dan tombol WhatsApp CS Toko.

---

### D. Pengendali JavaScript pada `resources/js/dashboard.js`

Ditambahkan modul kendali modal tracking dengan integrasi portalling DOM ke `document.body`:

```javascript
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

    // Kurir & Penerima
    const iconEl = document.getElementById('modalCourierIcon');
    if (iconEl) {
        iconEl.className = 'fa-solid ' + (data.courier_icon || 'fa-motorcycle') + ' text-success me-1';
    }
    const courierEl = document.getElementById('modalCourierName');
    if (courierEl) courierEl.textContent = data.courier_name || 'Kurir Toko / Pengiriman Standar';
    const recipientEl = document.getElementById('modalRecipientName');
    if (recipientEl) recipientEl.textContent = data.recipient_name || '-';

    // Status Badge & Stepper Checkpoints
    ...

    // Portalling ke body untuk mengeliminasi CSS Stacking Context Trap
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
```

---

### E. Standardisasi CSS Stacking Context pada `resources/css/pelanggan_pages.css` & `order_detail.blade.php`

Aturan CSS untuk backdrop dan dialog modal diperkuat dengan atribut `!important` untuk memastikan kepatuhan penuh terhadap viewport browser pada semua ukuran layar ($1568\text{px}$, $1440\text{px}$, maupun $390\text{px}$):

```css
.biteship-modal-backdrop {
    position: fixed !important;
    inset: 0 !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.65) !important;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: none;
    align-items: center !important;
    justify-content: center !important;
    z-index: 999999 !important;
    padding: 16px;
    margin: 0 !important;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.biteship-modal-backdrop.show {
    display: flex !important;
    opacity: 1 !important;
}

.biteship-modal-dialog {
    background: #ffffff !important;
    width: 100% !important;
    max-width: 580px !important;
    max-height: 90vh !important;
    margin: auto !important;
    position: relative !important;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
```

---

## 4. Hasil Verifikasi & Uji Otomatis

### 1. Kompilasi Aset Produksi (Vite)
Perintah `npm run build` dieksekusi dan menghasilkan aset terkompilasi bersih tanpa peringatan:
- `public/build/assets/dashboard-DmkM7ZQv.css` ($16.89\text{ kB}$)
- `public/build/assets/pelanggan_pages-CdeA8FZ0.css` ($133.06\text{ kB}$)
- `public/build/assets/dashboard-DmuTFxtB.js` ($25.85\text{ kB}$)
- `public/build/assets/pelanggan_pages-CTr8yAde.js` ($66.25\text{ kB}$)
- Durasi kompilasi: **517 ms**.

### 2. Pengujian Unit & Fitur Laravel (PHPUnit)
Perintah `php artisan test` dijalankan untuk seluruh suite pengujian proyek:
- `Tests\Feature\CustomerDashboardTest`: **4 passed** (20 assertions).
- `Tests\Feature\OrderDetailLayoutTest`: **7 passed** (35 assertions).
- `Tests\Feature\OrderHistoryTest`: **6 passed** (37 assertions).
- Total Keseluruhan Suite: **130 passed (653 assertions, 0 failures)**.

### 3. Validasi Pohon DOM (HTML Balancer)
Skrip verifikasi pasangan tag memvalidasi bahwa:
- `resources/views/Pelanggan/dashboard.blade.php`: `Total unclosed divs remaining: 0`.
- `resources/views/Pelanggan/order_detail.blade.php`: `Total unclosed divs remaining: 0`.
- Modal tidak lagi terperangkap di dalam kolom kiri dashboard, berpusat simetris di tengah layar ($x: 494\text{px}$, lebar $580\text{px}$ pada viewport $1568\text{px}$), dan tidak ada elemen footer yang tembus ke samping modal.
