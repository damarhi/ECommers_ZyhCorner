@extends('Layout.layout_pelanggan')

@section('title', 'FAQ - Zyh Corner')

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
                @forelse($faqs as $index => $faq)
                    <div class="faq-card" data-category="{{ $faq->kategori }}">
                        <div class="faq-q" onclick="toggleFaq({{ $index }})">
                            <span class="faq-title">{{ $faq->pertanyaan }}</span>
                            <span class="faq-icon" id="faqIcon{{ $index }}">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </div>
                        <div class="faq-a" id="faqAnswer{{ $index }}">
                            <div class="faq-a-inner">{!! nl2br(e($faq->jawaban)) !!}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 50px 20px; background: #fff; border-radius: 14px; border: 1px solid #e5e7eb;">
                        <p style="color: #94a3b8; margin: 0;">Belum ada pertanyaan.</p>
                    </div>
                @endforelse
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
                    <a href="https://wa.me/628123456789" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 24px; border: 2px solid rgba(255,255,255,0.5); color: #fff; border-radius: 50px; font-weight: 600; font-size: 14px; text-decoration: none; transition: transform 0.2s;">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('styles')
<style>
    .faq-tab {
        padding: 8px 18px;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .faq-tab:hover { border-color: #86efac; color: #15803d; }
    .faq-tab.active {
        background: #00b207;
        color: #fff;
        border-color: #00b207;
    }

    .faq-card {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        margin-bottom: 10px;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .faq-card:hover { border-color: #86efac; }
    .faq-card.open { border-color: #00b207; box-shadow: 0 4px 16px rgba(0,178,7,0.08); }

    .faq-q {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        cursor: pointer;
        gap: 12px;
    }
    .faq-title {
        font-weight: 600;
        font-size: 15px;
        color: #1e293b;
        line-height: 1.5;
        flex: 1;
    }
    .faq-card.open .faq-title { color: #00b207; }

    .faq-icon {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #f0fdf4;
        color: #00b207;
        font-size: 13px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .faq-card.open .faq-icon {
        background: #00b207;
        color: #fff;
        transform: rotate(180deg);
    }

    .faq-a {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease;
    }
    .faq-a-inner {
        padding: 0 20px 18px;
        font-size: 14px;
        line-height: 1.7;
        color: #475569;
        border-top: 1px solid #f1f5f9;
        padding-top: 14px;
        margin: 0 20px;
        padding-left: 0; padding-right: 0;
    }
</style>
@endsection

<script>
function toggleFaq(i) {
    var cards = document.querySelectorAll('.faq-card');
    cards.forEach(function(c, idx) {
        var a = document.getElementById('faqAnswer' + idx);
        if (idx === i && !c.classList.contains('open')) {
            c.classList.add('open');
            a.style.maxHeight = a.scrollHeight + 'px';
        } else {
            c.classList.remove('open');
            if (a) a.style.maxHeight = '0';
        }
    });
}

function filterCategory(cat, el) {
    document.querySelectorAll('.faq-tab').forEach(function(t) { t.classList.remove('active'); });
    el.classList.add('active');
    // close all
    document.querySelectorAll('.faq-card').forEach(function(c, idx) {
        c.classList.remove('open');
        var a = document.getElementById('faqAnswer' + idx);
        if (a) a.style.maxHeight = '0';
    });
    var vis = 0;
    document.querySelectorAll('.faq-card').forEach(function(c) {
        if (cat === 'All' || c.dataset.category === cat) { c.style.display = ''; vis++; }
        else { c.style.display = 'none'; }
    });
    document.getElementById('noResultsMsg').style.display = vis ? 'none' : 'block';
}

function filterFaqs() {
    var q = document.getElementById('faqSearchInput').value.toLowerCase().trim();
    var vis = 0;
    document.querySelectorAll('.faq-card').forEach(function(c) {
        var t = c.querySelector('.faq-title').innerText.toLowerCase();
        var a = c.querySelector('.faq-a-inner').innerText.toLowerCase();
        if (t.indexOf(q) > -1 || a.indexOf(q) > -1) { c.style.display = ''; vis++; }
        else { c.style.display = 'none'; }
    });
    document.getElementById('noResultsMsg').style.display = vis ? 'none' : 'block';
}
</script>
