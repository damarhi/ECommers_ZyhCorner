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
                        <p>{{ $storeSettings['store_address'] ?? 'Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294' }}</p>
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
                            <a href="mailto:{{ $storeSettings['store_email'] ?? 'zyhcorner@gmail.com' }}">{{ $storeSettings['store_email'] ?? 'zyhcorner@gmail.com' }}</a>
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
                            <a href="tel:+{{ $storeSettings['wa_number'] ?? '628123456789' }}">{{ $storeSettings['store_phone'] ?? '(+62) 812-3456-789' }}</a>
                        </p>
                    </div>
                </div>
                
            </div>
            
            <!-- Right Column: Just Say Hello Form -->
            <div class="contact-form-card">
                <h2>Just Say Hello!</h2>
                <p>Apakah Anda ingin menyapa kami atau memesan kue khusus untuk hari bahagia Anda? Jangan ragu untuk mengirimkan pesan!</p>
                
                <form id="contactForm" action="{{ route('contact.send') }}" method="POST" onsubmit="handleContactSubmit(event)">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" id="contactName" name="nama" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="contactEmail" name="email" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <input type="text" id="contactSubject" name="subjek" placeholder="Subjek Pesan" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <textarea id="contactMessage" name="pesan" placeholder="Tuliskan pesan Anda..." rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-send-message" id="btnSendContact">
                        <span>Send Message</span>
                    </button>
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
        
        const form = event.target;
        const btn = document.getElementById('btnSendContact');
        const originalBtnHtml = btn.innerHTML;
        
        const payload = {
            nama: document.getElementById('contactName').value.trim(),
            email: document.getElementById('contactEmail').value.trim(),
            subjek: document.getElementById('contactSubject').value.trim(),
            pesan: document.getElementById('contactMessage').value.trim()
        };
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        
        fetch('{{ route('contact.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            return response.json().then(data => ({ status: response.status, body: data }));
        })
        .then(({ status, body }) => {
            if (status >= 200 && status < 300 && body.success) {
                Swal.fire({
                    title: 'Pesan Terkirim!',
                    text: body.message || 'Terima kasih atas pesan Anda. Kami akan segera menghubungi Anda kembali.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'swal2-custom-font' }
                });
                form.reset();
            } else {
                let errMsg = body.message || 'Terjadi kesalahan saat mengirim pesan.';
                if (body.errors) {
                    const firstKey = Object.keys(body.errors)[0];
                    if (firstKey) errMsg = body.errors[firstKey][0];
                }
                Swal.fire({
                    title: 'Gagal Mengirim',
                    text: errMsg,
                    icon: 'error',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'swal2-custom-font' }
                });
            }
        })
        .catch(err => {
            Swal.fire({
                title: 'Koneksi Gagal',
                text: 'Gagal menghubungi server. Silakan periksa koneksi Anda dan coba lagi.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        });
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

            gsap.from('.contact-map-wrapper', {
                opacity: 0,
                duration: 0.9,
                ease: 'power3.out',
                delay: 0.3
            });
        }
    });
</script>

