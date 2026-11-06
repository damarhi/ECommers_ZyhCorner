@extends('Layout.layout_pelanggan')

@section('title', 'FAQ - Zyh Corner')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/css_faq.css') }}?v={{ file_exists(public_path('css/css_faq.css')) ? filemtime(public_path('css/css_faq.css')) : time() }}">
@endsection

@section('breadcrumb')
    <span style="color: #00b207; font-weight: 600;">FAQ</span>
@endsection

@section('content')
    <div class="faq-page">
        <div class="container faq-container">

            <!-- Search & Filter -->
            <div class="faq-controls">
                <div class="faq-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass faq-search-icon"></i>
                    <input type="text" id="faqSearchInput" class="faq-search-input" onkeyup="filterFaqs()"
                           placeholder="Cari pertanyaan...">
                </div>
                <div class="faq-tabs">
                    <button type="button" class="faq-tab active" onclick="filterCategory('All', this)">Semua</button>
                    <button type="button" class="faq-tab" onclick="filterCategory('Pemesanan', this)">Pemesanan</button>
                    <button type="button" class="faq-tab" onclick="filterCategory('Pengiriman', this)">Pengiriman</button>
                    <button type="button" class="faq-tab" onclick="filterCategory('Pembayaran', this)">Pembayaran</button>
                    <button type="button" class="faq-tab" onclick="filterCategory('Produk', this)">Produk</button>
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
            <div id="noResultsMsg" class="faq-no-results" style="display: none;">
                <p>Pertanyaan tidak ditemukan. Coba kata kunci lain.</p>
            </div>

            <!-- CTA -->
            <div class="faq-cta-card">
                <h3 class="faq-cta-title">Masih punya pertanyaan?</h3>
                <p class="faq-cta-desc">Hubungi tim kami untuk bantuan lebih lanjut.</p>
                <div class="faq-cta-actions">
                    <a href="{{ route('contact') }}" class="faq-cta-btn faq-cta-btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Hubungi Kami
                    </a>
                    <a href="https://wa.me/{{ $storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber() }}" target="_blank" rel="noopener noreferrer" class="faq-cta-btn faq-cta-btn-secondary">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/js_faq.js') }}?v={{ file_exists(public_path('js/js_faq.js')) ? filemtime(public_path('js/js_faq.js')) : time() }}"></script>
@endsection
