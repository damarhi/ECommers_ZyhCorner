@extends('Layout.layout_pelanggan')

@section('title', 'Return Order - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Return Order</span>
@endsection

@section('styles')
<!-- Load PurpleAdmin CSS and Icons -->
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">

<style>
    /* Reset clashing Bootstrap styles back to Zyh Corner custom styling */
    .breadcrumb-wrapper {
        background-color: white !important;
        padding: 20px 0 0 0 !important;
        display: block !important;
    }
    
    .breadcrumb-area {
        background: linear-gradient(rgba(45, 78, 55, 0.7), rgba(45, 78, 55, 0.7)), url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=2042&auto=format&fit=crop') center/cover !important;
        height: 120px !important;
        display: flex !important;
        align-items: center !important;
        color: white !important;
        border-radius: 8px !important;
        margin: 0 auto !important;
        max-width: 1200px !important;
        position: relative !important;
        overflow: hidden !important;
        padding: 0 !important;
    }

    .breadcrumb {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        font-size: 14px !important;
        padding-left: 40px !important;
        z-index: 1 !important;
        background: transparent !important;
        margin-bottom: 0 !important;
        border: none !important;
        border-radius: 0 !important;
    }

    .breadcrumb a {
        color: #e0e0e0 !important;
        text-decoration: none !important;
    }
    
    .breadcrumb span {
        color: #00b207 !important;
    }

    .breadcrumb i {
        color: #999 !important;
    }

    body {
        background-color: #ffffff !important;
        font-family: 'Poppins', sans-serif !important;
        color: #1a1a1a !important;
    }
    
    header, nav.navigation, footer.footer, .cart-sidebar {
        font-family: 'Poppins', sans-serif !important;
    }
    
    header a, nav.navigation a, footer.footer a, .cart-sidebar a {
        text-decoration: none !important;
    }
    
    nav.navigation a {
        color: #cccccc !important;
    }
    nav.navigation a:hover, nav.navigation a.active {
        color: white !important;
    }
    
    footer.footer {
        background-color: #1a1a1a !important;
        color: white !important;
        padding-top: 70px !important;
        margin-top: 0 !important;
        height: auto !important;
    }
    
    footer.footer h3 {
        color: white !important;
        font-size: 18px !important;
        font-weight: 500 !important;
    }
    
    footer.footer a {
        color: #999 !important;
    }
    footer.footer a:hover {
        color: white !important;
    }
    
    .search-bar button {
        background-color: #00b207 !important;
        color: white !important;
    }
    
    .search-bar input {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Return Order Page Layout */
    .return-wrapper {
        max-width: 1200px;
        margin: 50px auto 80px;
        padding: 0 15px;
    }

    .return-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .return-subtitle {
        font-size: 15px;
        color: #666;
        margin-bottom: 4px;
    }

    .return-deadline {
        font-size: 15px;
        color: #666;
        margin-bottom: 40px;
    }

    /* Main Content Grid */
    .return-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    /* Section Headers */
    .section-header {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a1a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 15px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e6e6e6;
    }

    /* Radio Options for Reason */
    .reason-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    .reason-option {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border: 1.5px solid #e6e6e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
        background: white;
    }

    .reason-option:hover {
        border-color: #00b207;
        background-color: #f8fff8;
    }

    .reason-option.selected {
        border-color: #00b207;
        background-color: #f0fdf0;
    }

    .reason-option input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d0d0d0;
        border-radius: 50%;
        position: relative;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }

    .reason-option input[type="radio"]:checked {
        border-color: #00b207;
    }

    .reason-option input[type="radio"]:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background-color: #00b207;
        border-radius: 50%;
    }

    .reason-option label {
        font-size: 14px;
        color: #333;
        cursor: pointer;
        margin: 0;
        flex: 1;
    }

    /* Textarea */
    .describe-textarea {
        width: 100%;
        min-height: 120px;
        padding: 14px 18px;
        border: 1.5px solid #e6e6e6;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        color: #1a1a1a;
        resize: vertical;
        outline: none;
        transition: border-color 0.3s;
    }

    .describe-textarea::placeholder {
        color: #aaa;
    }

    .describe-textarea:focus {
        border-color: #00b207;
        box-shadow: 0 0 0 3px rgba(0, 178, 7, 0.05);
    }

    /* Upload Photo Section */
    .upload-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px 20px;
    }

    .upload-area {
        width: 100%;
        min-height: 200px;
        border: 2px dashed #d0d0d0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fcfcfc;
        margin-bottom: 20px;
    }

    .upload-area:hover {
        border-color: #00b207;
        background: #f8fff8;
    }

    .upload-area.dragover {
        border-color: #00b207;
        background: #eafaea;
    }

    .upload-area.has-preview {
        border-style: solid;
        border-color: #00b207;
    }

    .upload-icon {
        font-size: 40px;
        color: #ccc;
        margin-bottom: 12px;
    }

    .upload-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 6px;
    }

    .upload-hint {
        font-size: 13px;
        color: #999;
        text-align: center;
        max-width: 280px;
    }

    .upload-preview {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 8px;
    }

    .btn-choose-photo {
        border: 2px solid #00b207;
        background-color: transparent;
        color: #00b207;
        padding: 10px 28px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .btn-choose-photo:hover {
        background-color: #00b207;
        color: white;
    }

    /* Return Method Section */
    .return-method-section {
        margin-bottom: 40px;
    }

    .method-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .method-option {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border: 1.5px solid #e6e6e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
        background: white;
        max-width: 600px;
    }

    .method-option:hover {
        border-color: #00b207;
        background-color: #f8fff8;
    }

    .method-option.selected {
        border-color: #00b207;
        background-color: #f0fdf0;
    }

    .method-option input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d0d0d0;
        border-radius: 50%;
        position: relative;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }

    .method-option input[type="radio"]:checked {
        border-color: #00b207;
    }

    .method-option input[type="radio"]:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background-color: #00b207;
        border-radius: 50%;
    }

    .method-option label {
        font-size: 14px;
        color: #333;
        cursor: pointer;
        margin: 0;
        flex: 1;
    }

    /* Action Buttons */
    .return-actions {
        display: flex;
        justify-content: flex-end;
        gap: 16px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #e6e6e6;
    }

    .btn-save-draft {
        border: 2px solid #00b207;
        background-color: transparent;
        color: #00b207;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .btn-save-draft:hover {
        background-color: #f0fdf0;
    }

    .btn-submit-return {
        border: none;
        background-color: #00b207;
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .btn-submit-return:hover {
        background-color: #009906;
    }

    .btn-submit-return:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .return-wrapper {
            margin: 20px auto 40px;
        }

        .return-deadline {
            margin-bottom: 20px;
        }

        .upload-area {
            min-height: 150px;
            padding: 20px 10px;
        }

        .return-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .return-title {
            font-size: 24px;
        }

        .return-actions {
            flex-direction: column-reverse;
            gap: 10px;
        }

        .btn-save-draft,
        .btn-submit-return {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="return-wrapper">
    
    <!-- Page Header -->
    <h1 class="return-title">Return Order {{ $order->order_number }}</h1>
    <p class="return-subtitle">Submit a return for a product already received</p>
    <p class="return-deadline">Return deadline: 24 hours after receiving the order</p>

    <form id="returnForm" onsubmit="submitReturnOrder(event)">
        
        <!-- Main Content: Reason + Upload Photo -->
        <div class="return-content">
            
            <!-- Left: Reason for Return -->
            <div class="reason-section">
                <div class="section-header">REASON FOR RETURN</div>
                
                <div class="reason-options">
                    <div class="reason-option selected" onclick="selectReason(this, 'doesnt_match')">
                        <input type="radio" name="reason" id="reason_match" value="Doesn't match the picture" checked>
                        <label for="reason_match">Doesn't match the picture</label>
                    </div>
                    <div class="reason-option" onclick="selectReason(this, 'disabled')">
                        <input type="radio" name="reason" id="reason_disabled" value="Disabled">
                        <label for="reason_disabled">Disabled</label>
                    </div>
                    <div class="reason-option" onclick="selectReason(this, 'wrong')">
                        <input type="radio" name="reason" id="reason_wrong" value="Wrong product">
                        <label for="reason_wrong">Wrong product</label>
                    </div>
                    <div class="reason-option" onclick="selectReason(this, 'others')">
                        <input type="radio" name="reason" id="reason_others" value="Others">
                        <label for="reason_others">Others</label>
                    </div>
                </div>

                <textarea class="describe-textarea" name="description" id="returnDescription" placeholder="Describe the problems..."></textarea>
            </div>

            <!-- Right: Upload Photo -->
            <div class="upload-photo-section">
                <div class="section-header">UPLOAD PHOTO</div>
                
                <div class="upload-section">
                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('returnPhotoInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up upload-icon" id="uploadIcon"></i>
                        <div class="upload-title" id="uploadTitle">Upload Photo</div>
                        <div class="upload-hint" id="uploadHint">Drag and drop or browse to upload a photo of the item you are returning.</div>
                        <img id="uploadPreview" class="upload-preview" style="display: none;" alt="Preview">
                    </div>
                    <input type="file" id="returnPhotoInput" name="photo" accept="image/*" style="display: none;" onchange="handlePhotoUpload(this)">
                    <button type="button" class="btn-choose-photo" onclick="document.getElementById('returnPhotoInput').click()">Choose Photo</button>
                </div>
            </div>

        </div>

        <!-- Return Method -->
        <div class="return-method-section">
            <div class="section-header">RETURN METHOD</div>
            
            <div class="method-options">
                <div class="method-option selected" onclick="selectMethod(this)">
                    <input type="radio" name="return_method" id="method_delivery" value="Send to Store With Delivery" checked>
                    <label for="method_delivery">Send to Store With Delivery</label>
                </div>
                <div class="method-option" onclick="selectMethod(this)">
                    <input type="radio" name="return_method" id="method_self" value="Send Yourself">
                    <label for="method_self">Send Yourself</label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="return-actions">
            <button type="button" class="btn-save-draft" onclick="saveDraft()">Save to Drafts</button>
            <button type="submit" class="btn-submit-return" id="btnSubmitReturn">Submit Return</button>
        </div>

    </form>

</div>

<script>
    // Select Reason
    function selectReason(el, value) {
        // Remove selected from all
        document.querySelectorAll('.reason-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        // Add selected to clicked
        el.classList.add('selected');
        // Check the radio
        el.querySelector('input[type="radio"]').checked = true;
    }

    // Select Return Method
    function selectMethod(el) {
        document.querySelectorAll('.method-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        el.classList.add('selected');
        el.querySelector('input[type="radio"]').checked = true;
    }

    // Photo Upload with Drag and Drop
    const uploadArea = document.getElementById('uploadArea');
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            document.getElementById('returnPhotoInput').files = files;
            handlePhotoUpload(document.getElementById('returnPhotoInput'));
        }
    });

    function handlePhotoUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('uploadPreview');
                const icon = document.getElementById('uploadIcon');
                const title = document.getElementById('uploadTitle');
                const hint = document.getElementById('uploadHint');
                const area = document.getElementById('uploadArea');
                
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                title.style.display = 'none';
                hint.style.display = 'none';
                area.classList.add('has-preview');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Save to Drafts
    function saveDraft() {
        const reason = document.querySelector('input[name="reason"]:checked')?.value || '';
        const description = document.getElementById('returnDescription').value;
        const method = document.querySelector('input[name="return_method"]:checked')?.value || '';

        const draftData = {
            order_id: '{{ $order->id }}',
            order_number: '{{ $order->order_number }}',
            reason: reason,
            description: description,
            method: method,
            saved_at: new Date().toISOString()
        };

        localStorage.setItem('zyh_return_draft_{{ $order->id }}', JSON.stringify(draftData));

        Swal.fire({
            title: 'Tersimpan!',
            text: 'Draft pengembalian berhasil disimpan.',
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Load Draft on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        const draft = localStorage.getItem('zyh_return_draft_{{ $order->id }}');
        if (draft) {
            try {
                const data = JSON.parse(draft);
                if (data.description) {
                    document.getElementById('returnDescription').value = data.description;
                }
                if (data.reason) {
                    document.querySelectorAll('.reason-option').forEach(opt => {
                        const radio = opt.querySelector('input[type="radio"]');
                        if (radio.value === data.reason) {
                            opt.classList.add('selected');
                            radio.checked = true;
                        } else {
                            opt.classList.remove('selected');
                        }
                    });
                }
                if (data.method) {
                    document.querySelectorAll('.method-option').forEach(opt => {
                        const radio = opt.querySelector('input[type="radio"]');
                        if (radio.value === data.method) {
                            opt.classList.add('selected');
                            radio.checked = true;
                        } else {
                            opt.classList.remove('selected');
                        }
                    });
                }
            } catch(e) {}
        }
    });

    // Submit Return Order
    function submitReturnOrder(e) {
        e.preventDefault();

        const reason = document.querySelector('input[name="reason"]:checked')?.value || '';
        const description = document.getElementById('returnDescription').value;
        const method = document.querySelector('input[name="return_method"]:checked')?.value || '';

        if (!reason) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Silakan pilih alasan pengembalian.',
                icon: 'warning',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
            return;
        }

        Swal.fire({
            title: 'Kirim Pengembalian?',
            text: 'Apakah Anda yakin ingin mengajukan pengembalian untuk pesanan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#00b207',
            cancelButtonColor: '#aaa',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch("{{ route('order.return.submit', $order->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        reason: reason,
                        description: description,
                        return_method: method
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        // Clear draft
                        localStorage.removeItem('zyh_return_draft_{{ $order->id }}');

                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Pengembalian berhasil diajukan.',
                            icon: 'success',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        }).then(() => {
                            window.location.href = "{{ route('order.detail', $order->id) }}";
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem.',
                        icon: 'error',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    });
                });
            }
        });
    }
</script>
@endsection
