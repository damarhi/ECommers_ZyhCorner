@extends('Layout.layout_pelanggan')

@section('title', 'About Us - Zyh Corner')

@section('breadcrumb')
    <span style="color: #00b207;">About Us</span>
@endsection

@section('content')
    <div class="about-container">
        
        <!-- Section 1: Intro Story -->
        <section class="about-intro mb-5">
            <div class="intro-text">
                <h2>100% Trusted Organic Food Store</h2>
                <p>Mulai dari kue ulang tahun, dessert box, hingga pastry hangat yang dipanggang segar setiap hari. Kami di Zyh Corner berkomitmen menyajikan kebahagiaan manis di setiap gigitan.</p>
                <p>Didirikan dengan kecintaan mendalam pada seni pembuatan kue, kami memadukan bahan-bahan alami terbaik dengan resep klasik yang telah disempurnakan. Zyh Corner bukan sekadar toko kue, melainkan tempat di mana setiap perayaan Anda dirayakan dengan kelezatan tak terlupakan.</p>
            </div>
            <div class="intro-image">
                <img src="{{ asset('image/about_cake_coffee.png') }}" alt="Zyh Corner Sweet Delights" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
            </div>
        </section>

        <!-- Section 2: Tujuan, Visi & Misi Toko -->
        <section class="about-features my-5">
            <div class="features-image">
                <img src="{{ asset('image/about_cake_showcase.png') }}" alt="Zyh Corner Fresh Showcase" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
            </div>
            <div class="features-content">
                <h2>Tujuan, Visi & Misi Kami</h2>
                <p>Kami mendedikasikan seluruh kreasi pastry & bakery terbaik untuk menghadirkan kebahagiaan manis di setiap momen berharga Anda.</p>
                
                <div class="features-grid" style="grid-template-columns: 1fr; gap: 20px;">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="feature-info">
                            <h3>Tujuan Usaha</h3>
                            <p style="white-space: pre-line;">Menjadi pilihan utama pelanggan dalam menikmati aneka pastry, dessert, dan kue segar berkualitas tinggi yang dibuat dengan bahan alami pilihan terbaik dan higienis.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-eye"></i></div>
                        <div class="feature-info">
                            <h3>Visi Kami</h3>
                            <p style="white-space: pre-line;">Menjadi toko pastry & bakery pilihan utama yang menghadirkan kebahagiaan manis di setiap momen istimewa keluarga dan pelanggan.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-list-check"></i></div>
                        <div class="feature-info">
                            <h3>Misi Kami</h3>
                            <p style="white-space: pre-line;">1. Menjaga kualitas rasa, kebersihan, dan kesegaran bahan baku terbaik.
2. Memberikan layanan ramah & profesional bagi setiap pelanggan.
3. Berinovasi menciptakan kreasi kue yang unik dan menarik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Delivery Promo -->
        <section class="about-delivery my-5">
            <div class="delivery-content">
                <h2>We Delivered, You Enjoy Your Order.</h2>
                <p>Kami memastikan setiap pesanan Anda sampai dalam kondisi sempurna, segar, dan tepat waktu untuk melengkapi perayaan Anda.</p>
                <ul class="delivery-list">
                    <li><i class="fa-solid fa-circle-check"></i> Pengiriman cepat dan aman hingga ke tangan Anda</li>
                    <li><i class="fa-solid fa-circle-check"></i> Kemasan kokoh, higienis, dan ramah lingkungan</li>
                    <li><i class="fa-solid fa-circle-check"></i> Garansi penggantian jika ada cacat pengiriman</li>
                </ul>
                <a href="{{ route('shop') }}" class="btn-shop-now">Shop Now <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
            <div class="delivery-image">
                <img src="{{ asset('image/about_store_front.png') }}" alt="Zyh Corner Storefront" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
            </div>
        </section>

        <!-- Section 4: Team Section -->
        <section class="about-team my-5">
            <div class="team-header text-center">
                <h2>Our Awesome Team</h2>
                <p>Tim profesional kami siap menyajikan produk bakery dan kue kualitas terbaik untuk Anda.</p>
            </div>
            <div class="team-grid-container">
                <div class="team-grid">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" alt="Jenny Wilson">
                        <h3>Jenny Wilson</h3>
                        <p>Senior Chef</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=300&auto=format&fit=crop" alt="Jane Cooper">
                        <h3>Jane Cooper</h3>
                        <p>Worker</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop" alt="Cody Fisher">
                        <h3>Cody Fisher</h3>
                        <p>Security Guard</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300&auto=format&fit=crop" alt="Robert Fox">
                        <h3>Robert Fox</h3>
                        <p>Senior Recipe Manager</p>
                    </div>
                </div>
                <!-- Team Dot Pagination Indicators -->
                <div class="team-dots">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                gsap.from('.about-intro .intro-text h2, .about-intro .intro-text p', {
                    y: 35,
                    opacity: 0,
                    duration: 0.8,
                    stagger: 0.14,
                    ease: 'power3.out'
                });

                gsap.from('.about-intro .intro-image', {
                    x: 60,
                    opacity: 0,
                    scale: 0.9,
                    duration: 1,
                    ease: 'power4.out',
                    delay: 0.15
                });
            }
        });
    </script>
@endsection

