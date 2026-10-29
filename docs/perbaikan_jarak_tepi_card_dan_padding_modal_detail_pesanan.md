# Laporan Perbaikan: Pengaturan Jarak Tepi Card (*Padding*) & Pelebaran Ruang Elemen Modal Detail Pesanan

## 1. Latar Belakang & Identifikasi Masalah

Pada tampilan sebelumnya, elemen-elemen di dalam kartu atas (*Informasi Pelanggan* dan *Status & Pembayaran*) terlihat menempel sangat rapat dengan garis batas tepi kartu (*card border*). 

Berdasarkan hasil inspeksi piksel pada tangkapan layar `local://image-6afb8922917342b.webp`:
- Jarak teks `@ INFORMASI PELANGGAN` dan `Nama Pelanggan` ke garis tepi kiri kartu tercatat **hanya 1 piksel** (`x=33` terhadap border `x=32`).
- Begitu pula dengan teks nilai di sisi kanan (`nama_nama`, `Transfer`, status pesanan), menempel ke garis tepi kanan kartu.

### Akar Masalah Teknis (*Technical Root Cause*)
Kartu sebelumnya menggunakan class utility `class="p-3.5 rounded-3 ..."`. 
Dalam kerangka kerja **Bootstrap 5**, utilitas spacing bawaan hanya menyediakan bilangan bulat `0` sampai `5` (`p-0` hingga `p-5`). Utility `p-3.5` tidak dikenali oleh mesin CSS browser, sehingga browser memperlakukannya sebagai **`padding: 0px`** (*zero padding*). Akibatnya, seluruh konten di dalam kartu kehilangan bantalan (*padding*) dan menempel langsung ke tepi garis luar kartu.

---

## 2. Implementasi Perbaikan Kode

**Berkas**: `resources/views/Admin/kelola_penjualan/order_detail_modal.blade.php`

### A. Penggantian Padding Kartu Menjadi Style Eksplisit
Mengganti `class="p-3.5 ..."` yang tidak valid menjadi gaya inline eksplisit `padding: 18px 20px;` dengan sudut melengkung `border-radius: 10px;`. Hal ini memberikan jaminan ruang bernapas $20\text{px}$ dari tepi kiri/kanan dan $18\text{px}$ dari tepi atas/bawah.

```blade
<!-- Sebelum (Padding 0 karena p-3.5 tidak valid di Bootstrap) -->
<div class="col-md-6">
    <div class="p-3.5 rounded-3 h-100 d-flex flex-column" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        ...
    </div>
</div>

<!-- Sesudah (Padding Eksplisit 18px vertikal, 20px horizontal) -->
<div class="col-md-6">
    <div class="h-100 d-flex flex-column" style="padding: 18px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        ...
    </div>
</div>
```

### B. Perapian Boks Catatan Pesanan & Baris Kurir
Memberikan jarak vertikal yang cukup (`mt-auto pt-3`) agar elemen bawah tidak berdempetan dengan alamat pengiriman maupun status pesanan:

```blade
<!-- Catatan Pesanan -->
@if($order->catatan_pesanan)
<div class="mt-auto pt-3">
    <span class="text-muted font-weight-500 d-block mb-1.5" style="font-size: 11.5px;">Catatan Pesanan</span>
    <div style="padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div class="d-flex align-items-start gap-2">
            <i class="mdi mdi-note-text-outline text-muted flex-shrink-0" style="font-size: 15px; margin-top: 1px; line-height: 1;"></i>
            <span class="text-secondary fst-italic" style="font-size: 12px; line-height: 1.5;">“{{ $order->catatan_pesanan }}”</span>
        </div>
    </div>
</div>
@endif

<!-- Kurir Pengiriman -->
@if($order->shipping_method)
<div class="d-flex justify-content-between align-items-center mt-auto pt-3">
    <span class="text-muted font-weight-500" style="font-size: 12px;">Kurir Pengiriman</span>
    <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 13px;">
        <i class="mdi mdi-truck-delivery-outline text-primary" style="font-size: 16px; line-height: 1;"></i>
        <span>{{ $order->shipping_method }}</span>
    </span>
</div>
@endif
```

### C. Pelebaran Padding Baris Tabel & Boks Tagihan
Meningkatkan padding sel tabel produk dari `10px 16px` menjadi `12px 18px`, serta menetapkan padding boks rincian tagihan terpadu menjadi `padding: 16px 20px;`:

```blade
<!-- THEAD Tabel -->
<tr>
    <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">PRODUK</th>
    <th class="text-center" style="width: 75px; padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">QTY</th>
    <th class="text-end" style="width: 150px; padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">SUBTOTAL</th>
</tr>

<!-- TBODY Tabel -->
<tr>
    <td style="padding: 12px 18px; white-space: normal; word-wrap: break-word;">
        <div class="font-weight-600 text-dark" style="font-size: 13px;">{{ $item['name'] }}</div>
        @if(!empty($item['custom_text']))
            <div class="badge badge-soft-info mt-1.5 d-inline-flex align-items-center gap-1.5" style="font-size: 11px; font-weight: 500; white-space: normal; text-align: left; max-width: 100%; padding: 3px 8px; border-radius: 4px;">
                <i class="mdi mdi-lead-pencil flex-shrink-0" style="font-size: 12px; line-height: 1;"></i>
                <span>Tulisan: "{{ $item['custom_text'] }}"</span>
            </div>
        @endif
    </td>
    <td class="text-center text-dark font-weight-600" style="padding: 12px 18px; font-size: 13px;">{{ $item['qty'] }}</td>
    <td class="text-end font-weight-600 text-dark" style="padding: 12px 18px; font-size: 13px;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
</tr>

<!-- Boks Rincian Tagihan Terintegrasi -->
<div class="border-top" style="padding: 16px 20px; background: #f8fafc; border-color: #e2e8f0 !important;">
    ...
</div>
```

### D. Penyelarasan Padding Outer Modal (Header, Body, Footer)
- Header: `style="padding: 18px 24px;"`
- Body: `style="padding: 24px; overflow-y: auto;"`
- Footer: `style="padding: 14px 24px;"`
- Tombol Aksi: `style="padding: 6px 18px; border-radius: 6px; font-weight: 500; font-size: 12.5px;"`

---

## 3. Hasil Pengujian & Verifikasi

1. **Inspeksi Integritas DOM Hasil Render**:
   - Struktur hirarki tepat 3 elemen anak tingkat pertama di bawah `.modal-content`: `.modal-header`, `.modal-body`, `.modal-footer`.
   - Kedua kartu atas terbukti memiliki style inline `padding: 18px 20px;` definitif sehingga tidak ada teks yang menempel ke garis tepi.
   - Boks tagihan terpadu terbukti memiliki style `padding: 16px 20px;`.
2. **Kompilasi Blade View Cache**:
   `php artisan view:clear && php artisan view:cache` &rarr; Berhasil 100%.
3. **Automated Test Suite (PHPUnit)**:
   `php artisan test` &rarr; **147 tests passed (741 assertions)** (100% *Green*).
