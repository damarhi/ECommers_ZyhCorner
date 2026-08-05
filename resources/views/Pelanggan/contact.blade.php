@extends('Layout.layout_pelanggan')

@section('title', 'Contact Us - Zyh Corner')

@section('breadcrumb')
    <span style="color: #00b207;">Contact</span>
@endsection

@section('content')
    <div class="contact-container">
        
        <!-- Upper Section: Info Cards and Contact Form -->
        <div class="contact-grid">
            
            <!-- Left Column: Contact Details Card -->
            <div class="contact-info-card">
                
                <!-- Address Section -->
                <div class="contact-info-section">
                    <div class="contact-info-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="contact-info-text">
                        <h3>Alamat Toko</h3>
                        <p>Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294</p>
                    </div>
                </div>
                
                <hr class="contact-divider">
                
                <!-- Email Section -->
                <div class="contact-info-section">
                    <div class="contact-info-icon">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <div class="contact-info-text">
                        <h3>Surel Kami</h3>
                        <p>
                            <a href="mailto:zyhcorner@gmail.com">zyhcorner@gmail.com</a><br>
                            <a href="mailto:support@zyhcorner.com">support@zyhcorner.com</a>
                        </p>
                    </div>
                </div>
                
                <hr class="contact-divider">
                
                <!-- Phone Section -->
                <div class="contact-info-section">
                    <div class="contact-info-icon">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <div class="contact-info-text">
                        <h3>Nomor Telepon</h3>
                        <p>
                            <a href="tel:+628123456789">(+62) 812-3456-789</a><br>
                            <a href="tel:+628117654321">(+62) 811-7654-321</a>
                        </p>
                    </div>
                </div>
                
            </div>
            
            <!-- Right Column: Just Say Hello Form -->
            <div class="contact-form-card">
                <h2>Just Say Hello!</h2>
                <p>Apakah Anda ingin menyapa kami atau memesan kue khusus untuk hari bahagia Anda? Jangan ragu untuk mengirimkan pesan!</p>
                
                <form id="contactForm" onsubmit="handleContactSubmit(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" id="contactName" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="contactEmail" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <input type="text" id="contactSubject" placeholder="Subjek Pesan" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <textarea id="contactMessage" placeholder="Tuliskan pesan Anda..." rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-send-message">Send Message</button>
                </form>
            </div>
            
        </div>
        
        <!-- Lower Section: Google Map Location -->
        <div class="contact-map-wrapper">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7192150925703!2d101.85587509999999!3d0.40384089999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5c5c32fbe821f%3A0xcc0417290c322aec!2sZyh%20Corner!5e0!3m2!1sen!2sid!4v1782150424180!5m2!1sen!2sid" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        
    </div>
@endsection

@section('styles')
    <style>
        /* Inline custom overrides or script integrations for Contact Page */
    </style>
@endsection

<script>
    function handleContactSubmit(event) {
        event.preventDefault();
        
        // Show success alert using SweetAlert2
        Swal.fire({
            title: 'Pesan Terkirim!',
            text: 'Terima kasih atas pesan Anda. Kami akan segera menghubungi Anda kembali.',
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: {
                popup: 'swal2-custom-font'
            }
        });
        
        // Reset form
        event.target.reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof gsap !== 'undefined') {
            gsap.from('.contact-info-card', {
                x: -50,
                opacity: 0,
                duration: 0.85,
                ease: 'power3.out'
            });

            gsap.from('.contact-form-card', {
                x: 50,
                opacity: 0,
                duration: 0.85,
                ease: 'power3.out',
                delay: 0.15
            });

            gsap.from('.contact-map-container', {
                y: 40,
                opacity: 0,
                duration: 0.9,
                ease: 'power3.out',
                delay: 0.3
            });
        }
    });
</script>

