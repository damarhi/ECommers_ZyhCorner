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
                <h2>{{ (isset($about) && $about->judul_utama) ? $about->judul_utama : '100% Trusted Organic Food Store' }}</h2>
                @if(isset($about) && $about->subjudul_utama)
                    <h5 class="mb-3" style="color: #00b207; font-weight: 600; font-size: 18px;">{{ $about->subjudul_utama }}</h5>
                @endif
                @if(isset($about) && $about->cerita_singkat)
                    <p style="white-space: pre-line; line-height: 1.8; color: #666;">{{ $about->cerita_singkat }}</p>
                @else
                    <p>Mulai dari kue ulang tahun, dessert box, hingga pastry hangat yang dipanggang segar setiap hari. Kami di Zyh Corner berkomitmen menyajikan kebahagiaan manis di setiap gigitan.</p>
                    <p>Didirikan dengan kecintaan mendalam pada seni pembuatan kue, kami memadukan bahan-bahan alami terbaik dengan resep klasik yang telah disempurnakan. Zyh Corner bukan sekadar toko kue, melainkan tempat di mana setiap perayaan Anda dirayakan dengan kelezatan tak terlupakan.</p>
                @endif
            </div>
            <div class="intro-image">
                @if(isset($about) && $about->gambar_tentang && Storage::disk('public')->exists($about->gambar_tentang))
                    <img src="{{ asset('storage/' . $about->gambar_tentang) }}" alt="{{ $about->judul_utama }}" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @else
                    <img src="{{ asset('image/about_cake_coffee.png') }}" alt="Zyh Corner Sweet Delights" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @endif
            </div>
        </section>

        <!-- Section 2: Tujuan, Visi & Misi Toko -->
        <section class="about-features my-5">
            <div class="features-image">
                @if(isset($about) && $about->foto_galeri_1 && Storage::disk('public')->exists($about->foto_galeri_1))
                    <img src="{{ asset('storage/' . $about->foto_galeri_1) }}" alt="Zyh Corner Showcase" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @else
                    <img src="{{ asset('image/about_cake_showcase.png') }}" alt="Zyh Corner Fresh Showcase" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @endif
            </div>
            <div class="features-content">
                <h2>{{ (isset($about) && $about->judul_fitur) ? $about->judul_fitur : 'Tujuan, Visi & Misi Kami' }}</h2>
                <p>Kami mendedikasikan seluruh kreasi pastry & bakery terbaik untuk menghadirkan kebahagiaan manis di setiap momen berharga Anda.</p>
                
                <div class="features-grid" style="grid-template-columns: 1fr; gap: 20px;">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="feature-info">
                            <h3>Tujuan Usaha</h3>
                            <p style="white-space: pre-line;">{{ (isset($about) && $about->deskripsi_fitur) ? $about->deskripsi_fitur : 'Menjadi pilihan utama pelanggan dalam menikmati aneka pastry, dessert, dan kue segar berkualitas tinggi yang dibuat dengan bahan alami pilihan terbaik dan higienis.' }}</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-eye"></i></div>
                        <div class="feature-info">
                            <h3>Visi Kami</h3>
                            <p style="white-space: pre-line;">{{ (isset($about) && $about->visi) ? $about->visi : 'Menjadi toko pastry & bakery pilihan utama yang menghadirkan kebahagiaan manis di setiap momen istimewa keluarga dan pelanggan.' }}</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-list-check"></i></div>
                        <div class="feature-info">
                            <h3>Misi Kami</h3>
                            <p style="white-space: pre-line;">{{ (isset($about) && $about->misi) ? $about->misi : "1. Menjaga kualitas rasa, kebersihan, dan kesegaran bahan baku terbaik.\n2. Memberikan layanan ramah & profesional bagi setiap pelanggan.\n3. Berinovasi menciptakan kreasi kue yang unik dan menarik." }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Delivery Promo -->
        <section class="about-delivery my-5">
            <div class="delivery-content">
                <h2>{{ (isset($about) && $about->judul_delivery) ? $about->judul_delivery : 'We Delivered, You Enjoy Your Order.' }}</h2>
                <p>
                    @if(isset($about) && $about->deskripsi_delivery)
                        {{ $about->deskripsi_delivery }}
                    @else
                        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                    @endif
                </p>
                <ul class="delivery-list">
                    <li><i class="fa-solid fa-circle-check"></i> {{ (isset($about) && $about->poin_delivery_1) ? $about->poin_delivery_1 : 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem' }}</li>
                    <li><i class="fa-solid fa-circle-check"></i> {{ (isset($about) && $about->poin_delivery_2) ? $about->poin_delivery_2 : 'Kemasan kokoh, higienis, dan ramah lingkungan' }}</li>
                    <li><i class="fa-solid fa-circle-check"></i> {{ (isset($about) && $about->poin_delivery_3) ? $about->poin_delivery_3 : 'Garansi penggantian jika ada cacat pengiriman' }}</li>
                </ul>
                <a href="{{ route('shop') }}" class="btn-shop-now">Shop Now <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
            <div class="delivery-image">
                @if(isset($about) && $about->foto_galeri_2 && Storage::disk('public')->exists($about->foto_galeri_2))
                    <img src="{{ asset('storage/' . $about->foto_galeri_2) }}" alt="Zyh Corner Storefront" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @else
                    <img src="{{ asset('image/about_store_front.png') }}" alt="Zyh Corner Storefront" style="border-radius: 12px; object-fit: cover; width: 100%; height: 380px;">
                @endif
            </div>
        </section>

        <!-- Section 4: Team Section -->
        <section class="about-team my-5">
            <div class="team-header text-center">
                <h2>{{ (isset($about) && $about->judul_tim) ? $about->judul_tim : 'Our Awesome Team' }}</h2>
                <p>
                    @if(isset($about) && $about->deskripsi_tim)
                        {{ $about->deskripsi_tim }}
                    @else
                        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante.
                    @endif
                </p>
            </div>
            <div class="team-grid-container">
                <div class="team-grid">
                    @if(isset($teamMembers) && count($teamMembers) > 0)
                        @foreach($teamMembers as $member)
                            <div class="team-card">
                                @if($member->foto && Storage::disk('public')->exists($member->foto))
                                    <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}">
                                @else
                                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=300&auto=format&fit=crop" alt="{{ $member->nama }}">
                                @endif
                                <h3>{{ $member->nama }}</h3>
                                <p>{{ $member->jabatan }}</p>
                            </div>
                        @endforeach
                    @else
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
                    @endif
                </div>
                <!-- Team Dot Pagination Indicators -->
                <div class="team-dots">
                    @php $count = isset($teamMembers) && count($teamMembers) > 0 ? count($teamMembers) : 4; @endphp
                    @for($i = 0; $i < $count; $i++)
                        <span class="dot {{ $i === 0 ? 'active' : '' }}"></span>
                    @endfor
                </div>
            </div>
        </section>

    </div>
@endsection
