@extends('Layout.layout_pelanggan')

@section('title', 'FAQ - Zyh Corner')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/css_faq.css') }}?v={{ file_exists(public_path('css/css_faq.css')) ? filemtime(public_path('css/css_faq.css')) : time() }}">
@endsection

@section('breadcrumb')
    <span style="color: #00b207; font-weight: 600;">FAQ</span>
@endsection

@section('content')
    <div class="faq-page" style="background: #f8faf9; padding: 40px 0 60px;">
        <div class="container" style="max-width: 820px;">

            <!-- Search & Filter -->
            <div style="margin-bottom: 28px;">
                <div style="position: relative; margin-bottom: 20px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #00b207; font-size: 16px;"></i>
                    <input type="text" id="faqSearchInput" onkeyup="filterFaqs()" 
                           placeholder="Cari pertanyaan..." 
                           style="width: 100%; padding: 14px 18px 14px 46px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; outline: none; background: #fff; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='#00b207'" onblur="this.style.borderColor='#e5e7eb'">
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="faq-tab active" onclick="filterCategory('All', this)">Semua</button>
                    <button class="faq-tab" onclick="filterCategory('Pemesanan', this)">Pemesanan</button>
                    <button class="faq-tab" onclick="filterCategory('Pengiriman', this)">Pengiriman</button>
                    <button class="faq-tab" onclick="filterCategory('Pembayaran', this)">Pembayaran</button>
                    <button class="faq-tab" onclick="filterCategory('Produk', this)">Produk</button>
                </div>
            </div>

            <!-- FAQ List -->
            <div id="faqAccordion">
                <div class="faq-card" data-category="Pemesanan">
                    <div class="faq-q" onclick="toggleFaq(0)">
                        <span class="faq-title">Bagaimana cara melakukan pemesanan kue di Zyh Corner?</span>
                        <span class="faq-icon" id="faqIcon0"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer0">
                        <div class="faq-a-inner">Anda dapat memilih produk kue favorit Anda melalui halaman Shop, masukkan ke keranjang belanja, lalu selesaikan proses checkout secara mudah.</div>
                    </div>
                </div>

                <div class="faq-card" data-category="Pemesanan">
                    <div class="faq-q" onclick="toggleFaq(1)">
                        <span class="faq-title">Apakah bisa pesan kue custom sesuai keinginan?</span>
                        <span class="faq-icon" id="faqIcon1"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer1">
                        <div class="faq-a-inner">Tentu saja! Kami menerima pesanan custom kue ulang tahun dan event khusus melalui menu Custom Products atau langsung hubungi WhatsApp kami.</div>
                    </div>
                </div>

                <div class="faq-card" data-category="Pengiriman">
                    <div class="faq-q" onclick="toggleFaq(2)">
                        <span class="faq-title">Bagaimana proses dan estimasi pengiriman kue?</span>
                        <span class="faq-icon" id="faqIcon2"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer2">
                        <div class="faq-a-inner">Pengiriman dilakukan via kurir instan/khusus makanan untuk menjamin kondisi kue tetap segar dan tidak rusak sampai di tujuan Anda.</div>
                    </div>
                </div>

                <div class="faq-card" data-category="Pengiriman">
                    <div class="faq-q" onclick="toggleFaq(3)">
                        <span class="faq-title">Apakah kemasan pengiriman aman?</span>
                        <span class="faq-icon" id="faqIcon3"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer3">
                        <div class="faq-a-inner">Ya, setiap produk dikemas menggunakan kemasan kokoh, higienis, dan dilengkapi penyangga khusus untuk memastikan keamanan saat diperjalanan.</div>
                    </div>
                </div>

                <div class="faq-card" data-category="Pembayaran">
                    <div class="faq-q" onclick="toggleFaq(4)">
                        <span class="faq-title">Metode pembayaran apa saja yang didukung?</span>
                        <span class="faq-icon" id="faqIcon4"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer4">
                        <div class="faq-a-inner">Kami mendukung pembayaran online praktis via Midtrans (Transfer Bank, QRIS, e-Wallet GoPay/ShopeePay) serta pembayaran tunai saat transaksi langsung di toko.</div>
                    </div>
                </div>

                <div class="faq-card" data-category="Produk">
                    <div class="faq-q" onclick="toggleFaq(5)">
                        <span class="faq-title">Apakah bahan-bahan kue yang digunakan halal & berkualitas?</span>
                        <span class="faq-icon" id="faqIcon5"><i class="fa-solid fa-chevron-down"></i></span>
                    </div>
                    <div class="faq-a" id="faqAnswer5">
                        <div class="faq-a-inner">Pasti! Zyh Corner hanya menggunakan bahan baku 100% premium, halal, dan tanpa pengawet buatan untuk menjaga rasa alami dan kesegaran terbaik.</div>
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <div id="noResultsMsg" style="display: none; text-align: center; padding: 50px 20px; background: #fff; border-radius: 14px; border: 1px solid #e5e7eb; margin-top: 12px;">
                <p style="color: #94a3b8; margin: 0;">Pertanyaan tidak ditemukan. Coba kata kunci lain.</p>
            </div>

            <!-- CTA -->
            <div style="margin-top: 40px; padding: 36px 28px; border-radius: 16px; text-align: center; color: #fff; background: linear-gradient(135deg, #00b207, #15803d); box-shadow: 0 8px 24px rgba(0,178,7,0.2);">
                <h3 style="font-weight: 700; font-size: 20px; margin: 0 0 8px;">Masih punya pertanyaan?</h3>
                <p style="opacity: 0.9; font-size: 14px; margin: 0 0 20px;">Hubungi tim kami untuk bantuan lebih lanjut.</p>
                <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('contact') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 24px; background: #fff; color: #00b207; border-radius: 50px; font-weight: 600; font-size: 14px; text-decoration: none; transition: transform 0.2s;">
                        <i class="fa-solid fa-paper-plane"></i> Hubungi Kami
                    </a>
                    <a href="https://wa.me/{{ $storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber() }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 24px; border: 2px solid rgba(255,255,255,0.5); color: #fff; border-radius: 50px; font-weight: 600; font-size: 14px; text-decoration: none; transition: transform 0.2s;">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('styles')

@endsection

@section('scripts')
<script src="{{ asset('js/js_faq.js') }}?v={{ file_exists(public_path('js/js_faq.js')) ? filemtime(public_path('js/js_faq.js')) : time() }}"></script>
@endsection
