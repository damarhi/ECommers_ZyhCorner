@extends('Layout.layout_pelanggan')

@section('title', 'About Us - Zyh Corner')

@section('breadcrumb')
    <span style="color: #00b207;">About Us</span>
@endsection

@section('content')
    <div class="about-container">
        
        <!-- Section 1: Intro Story -->
        <section class="about-intro">
            <div class="intro-text">
                <div class="about-section-badge">
                    <i class="fa-solid fa-cake-candles"></i> Kisah & Tradisi Rasa
                </div>
                <h2>100% Trusted Organic Food Store</h2>
                <p>Mulai dari kue ulang tahun, dessert box, hingga pastry hangat yang dipanggang segar setiap hari. Kami di Zyh Corner berkomitmen menyajikan kebahagiaan manis di setiap gigitan.</p>
                <p>Didirikan dengan kecintaan mendalam pada seni pembuatan kue, kami memadukan bahan-bahan alami terbaik dengan resep klasik yang telah disempurnakan. Zyh Corner bukan sekadar toko kue, melainkan tempat di mana setiap perayaan Anda dirayakan dengan kelezatan tak terlupakan.</p>
            </div>
            <div class="intro-image">
                <img src="{{ asset('image/about_cake_coffee.png') }}" alt="Zyh Corner Sweet Delights" class="about-hero-img">
            </div>
        </section>

        <!-- Section 2: Tujuan, Visi & Misi Toko -->
        <section class="about-features">
            <div class="features-image">
                <img src="{{ asset('image/about_cake_showcase.png') }}" alt="Zyh Corner Fresh Showcase" class="about-hero-img">
            </div>
            <div class="features-content">
                <div class="about-section-badge">
                    <i class="fa-solid fa-compass"></i> Filosofi & Komitmen Kami
                </div>
                <h2>Tujuan, Visi & Misi Kami</h2>
                <p class="features-lead">Kami mendedikasikan seluruh kreasi pastry & bakery terbaik untuk menghadirkan kebahagiaan manis di setiap momen berharga Anda.</p>
                
                <div class="about-values-cards">
                    <!-- Kartu Tujuan Usaha -->
                    <div class="about-value-card card-tujuan">
                        <div class="value-card-header">
                            <div class="value-icon"><i class="fa-solid fa-bullseye"></i></div>
                            <div class="value-title-wrap">
                                <span class="value-pill">Tujuan Utama</span>
                                <h3>Tujuan Usaha</h3>
                            </div>
                        </div>
                        <p class="value-desc">Menjadi pilihan utama pelanggan dalam menikmati aneka pastry, dessert, dan kue segar berkualitas tinggi yang dibuat dengan bahan alami pilihan terbaik dan higienis.</p>
                    </div>

                    <!-- Kartu Visi Kami -->
                    <div class="about-value-card card-visi">
                        <div class="value-card-header">
                            <div class="value-icon"><i class="fa-solid fa-eye"></i></div>
                            <div class="value-title-wrap">
                                <span class="value-pill">Arah Masa Depan</span>
                                <h3>Visi Kami</h3>
                            </div>
                        </div>
                        <p class="value-desc">Menjadi toko pastry & bakery pilihan utama yang menghadirkan kebahagiaan manis di setiap momen istimewa keluarga dan pelanggan.</p>
                    </div>

                    <!-- Kartu Misi Kami -->
                    <div class="about-value-card card-misi">
                        <div class="value-card-header">
                            <div class="value-icon"><i class="fa-solid fa-list-check"></i></div>
                            <div class="value-title-wrap">
                                <span class="value-pill">Langkah Nyata</span>
                                <h3>Misi Kami</h3>
                            </div>
                        </div>
                        <div class="mission-steps-list">
                            <div class="mission-step-item">
                                <span class="step-num">1</span>
                                <span class="step-text">Menjaga kualitas rasa, kebersihan, dan kesegaran bahan baku terbaik.</span>
                            </div>
                            <div class="mission-step-item">
                                <span class="step-num">2</span>
                                <span class="step-text">Memberikan layanan ramah & profesional bagi setiap pelanggan.</span>
                            </div>
                            <div class="mission-step-item">
                                <span class="step-num">3</span>
                                <span class="step-text">Berinovasi menciptakan kreasi kue yang unik dan menarik.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Delivery Promo -->
        <section class="about-delivery">
            <div class="delivery-content">
                <div class="about-section-badge">
                    <i class="fa-solid fa-truck-fast"></i> Jaminan Layanan & Pengiriman
                </div>
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
                <img src="{{ asset('image/about_store_front.png') }}" alt="Zyh Corner Storefront" class="about-hero-img">
            </div>
        </section>

        <!-- Section 4: Team Section -->
        <section class="about-team">
            <div class="team-header text-center">
                <div class="about-section-badge mx-auto">
                    <i class="fa-solid fa-users"></i> Tim Profesional Kami
                </div>
                <h2>Our Awesome Team</h2>
                <p>Tim pastry chef dan baker berdedikasi kami siap menyajikan produk bakery dan kue kualitas terbaik untuk Anda.</p>
            </div>
            <div class="team-grid-container">
                <div class="team-grid">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" alt="Jenny Wilson">
                        <h3>Jenny Wilson</h3>
                        <p class="team-role">Head Pastry Chef</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=300&auto=format&fit=crop" alt="Jane Cooper">
                        <h3>Jane Cooper</h3>
                        <p class="team-role">Artisan Cake Decorator</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop" alt="Cody Fisher">
                        <h3>Cody Fisher</h3>
                        <p class="team-role">Master Baker</p>
                    </div>
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300&auto=format&fit=crop" alt="Robert Fox">
                        <h3>Robert Fox</h3>
                        <p class="team-role">Senior Recipe Specialist</p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                gsap.from('.about-intro .intro-text h2, .about-intro .intro-text p, .about-intro .about-section-badge', {
                    y: 25,
                    opacity: 0,
                    duration: 0.8,
                    stagger: 0.12,
                    ease: 'power3.out',
                    clearProps: 'all'
                });

                gsap.from('.about-intro .intro-image', {
                    y: 25,
                    opacity: 0,
                    scale: 0.98,
                    duration: 0.85,
                    ease: 'power3.out',
                    delay: 0.15,
                    clearProps: 'all'
                });
            }
        });
    </script>
@endsection

