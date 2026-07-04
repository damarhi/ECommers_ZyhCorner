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
                            <input type="radio" name="reason" id="reason_match" value="Doesn't match the picture"
                                checked>
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
                        <div class="upload-area" id="uploadArea"
                            onclick="document.getElementById('returnPhotoInput').click()">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon" id="uploadIcon"></i>
                            <div class="upload-title" id="uploadTitle">Upload Photo</div>
                            <div class="upload-hint" id="uploadHint">Drag and drop or browse to upload a photo of the item
                                you are returning.</div>
                            <img id="uploadPreview" class="upload-preview" style="display: none;" alt="Preview">
                        </div>
                        <input type="file" id="returnPhotoInput" name="photo" accept="image/*" style="display: none;"
                            onchange="handlePhotoUpload(this)">
                        <button type="button" class="btn-choose-photo"
                            onclick="document.getElementById('returnPhotoInput').click()">Choose Photo</button>
                    </div>
                </div>

            </div>

            <!-- Return Method -->
            <div class="return-method-section">
                <div class="section-header">RETURN METHOD</div>

                <div class="method-options">
                    <div class="method-option selected" onclick="selectMethod(this)">
                        <input type="radio" name="return_method" id="method_delivery" value="Send to Store With Delivery"
                            checked>
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
        window.returnOrderConfig = {
            orderId: '{{ $order->id }}',
            orderNumber: '{{ $order->order_number }}',
            returnSubmitRoute: "{{ route('order.return.submit', $order->id) }}",
            orderDetailRoute: "{{ route('order.detail', $order->id) }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
@endsection
