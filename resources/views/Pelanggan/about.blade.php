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
                <h2>Cita Rasa Klasik dengan Sentuhan Premium</h2>
                <p>Mulai dari kue ulang tahun, dessert box, hingga pastry hangat yang dipanggang segar setiap hari. Kami di Zyh Corner berkomitmen menyajikan kebahagiaan manis di setiap gigitan.</p>
                <p>Didirikan dengan kecintaan mendalam pada seni pembuatan kue, kami memadukan bahan-bahan alami terbaik dengan resep klasik yang telah disempurnakan. Zyh Corner bukan sekadar toko kue, melainkan tempat di mana setiap perayaan Anda dirayakan dengan kelezatan tak terlupakan.</p>
            </div>
            <div class="intro-image">
                <img src="{{ asset('image/about_cake_coffee.png') }}" alt="Zyh Corner Sweet Delights">
            </div>
        </section>

        <!-- Section 2: Features Grid -->
        <section class="about-features">
            <div class="features-image">
                <img src="{{ asset('image/about_cake_showcase.png') }}" alt="Zyh Corner Fresh Showcase">
            </div>
            <div class="features-content">
                <h2>Mengapa Memilih Zyh Corner?</h2>
                <p>Kami percaya bahwa produk berkualitas lahir dari dedikasi dan standar tinggi. Berikut adalah keunggulan yang kami tawarkan:</p>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-seedling"></i></div>
                        <div class="feature-info">
                            <h3>Bahan 100% Pilihan</h3>
                            <p>Menggunakan mentega premium, cokelat murni, dan buah-buahan segar tanpa kompromi.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                        <div class="feature-info">
                            <h3>Dukungan Cepat 24/7</h3>
                            <p>Tim admin kami siap membantu pesanan kustom dan layanan informasi kapan saja.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-thumbs-up"></i></div>
                        <div class="feature-info">
                            <h3>Umpan Balik Positif</h3>
                            <p>Dipercaya oleh ribuan pelanggan setia dengan tingkat kepuasan rasa yang tinggi.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-credit-card"></i></div>
                        <div class="feature-info">
                            <h3>Pembayaran Aman</h3>
                            <p>Mendukung berbagai metode pembayaran digital yang praktis dan terverifikasi.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="feature-info">
                            <h3>Bebas Pengawet</h3>
                            <p>Kue kami dibuat murni tanpa pengawet buatan sehingga aman dikonsumsi keluarga.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <div class="feature-info">
                            <h3>Selalu Segar</h3>
                            <p>Kue hanya dipanggang setelah dipesan (bake-to-order) untuk menjaga kualitas rasa.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Delivery Promo -->
        <section class="about-delivery">
            <div class="delivery-content">
                <h2>Kami Kirim Kehangatan, Anda Nikmati Kelezatannya</h2>
                <p>Tidak perlu khawatir kue rusak di jalan. Zyh Corner menyediakan kurir khusus dengan penanganan makanan profesional untuk memastikan pesanan Anda tiba dengan bentuk yang cantik dan rasa yang segar.</p>
                <ul class="delivery-list">
                    <li><i class="fa-solid fa-circle-check"></i> Pengiriman cepat & aman dengan kurir khusus</li>
                    <li><i class="fa-solid fa-circle-check"></i> Kemasan kokoh, higienis, dan ramah lingkungan</li>
                    <li><i class="fa-solid fa-circle-check"></i> Garansi penggantian jika ada cacat pengiriman</li>
                </ul>
                <a href="{{ route('shop') }}" class="btn-shop-now">Belanja Sekarang <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="delivery-image">
                <img src="{{ asset('image/about_store_front.png') }}" alt="Zyh Corner Storefront">
            </div>
        </section>

        <!-- Section 4: Team Section -->
        <section class="about-team">
            <div class="team-header">
                <h2>Tim Hebat Kami</h2>
                <p>Koki pastry dan desainer dekorasi berbakat yang mendedikasikan waktu mereka untuk kreasi manis di Zyh Corner.</p>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" alt="Jenny Wilson">
                    <h3>Jenny Wilson</h3>
                    <p>Founder & Head Baker</p>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=300&auto=format&fit=crop" alt="Jane Cooper">
                    <h3>Jane Cooper</h3>
                    <p>Cake Decorator</p>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=300&auto=format&fit=crop" alt="Cody Fisher">
                    <h3>Cody Fisher</h3>
                    <p>Outlet Manager</p>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=300&auto=format&fit=crop" alt="Robert Fox">
                    <h3>Robert Fox</h3>
                    <p>Pastry Chef</p>
                </div>
            </div>
        </section>

        <!-- Section 5: Testimonials -->
        <section class="about-testimonials">
            <div class="testimonial-header">
                <h2>Testimoni Pelanggan</h2>
                <p>Apa kata mereka yang telah merasakan kelezatan kue dan pelayanan prima dari Zyh Corner.</p>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                    <p>"Kue ulang tahun kustom dari Zyh Corner sangat mengagumkan! Desainnya persis seperti yang saya minta dan rasa cokelat layernya sangat premium, tidak kemanisan."</p>
                    <div class="testimonial-user">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=150&auto=format&fit=crop" alt="Robert Fox">
                        <div class="user-info">
                            <h4>Robert Fox</h4>
                            <span>Pelanggan Setia</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                    <p>"Matcha latte dan strawberry shortcake-nya adalah kombinasi terbaik di sore hari. Tempatnya bersih dan pengiriman pesanannya sangat cepat serta rapi."</p>
                    <div class="testimonial-user">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=150&auto=format&fit=crop" alt="Dianne Russell">
                        <div class="user-info">
                            <h4>Dianne Russell</h4>
                            <span>Pecinta Cake</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                    <p>"Sangat merekomendasikan order via WhatsApp untuk pesanan kustom. Respon admin sangat cepat, ramah, dan solutif membantu menentukan ukuran kue."</p>
                    <div class="testimonial-user">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=150&auto=format&fit=crop" alt="Eleanor Pena">
                        <div class="user-info">
                            <h4>Eleanor Pena</h4>
                            <span>Ibu Rumah Tangga</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
