<!-- Modal Verifikasi Bukti Pembayaran -->
<div class="modal fade" id="verifyProofModal{{ $order->id }}" tabindex="-1" aria-labelledby="verifyProofModalLabel{{ $order->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 780px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom bg-white flex-shrink-0" style="padding: 18px 24px;">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="d-inline-flex align-items-center justify-content-center text-warning rounded-circle" style="width: 38px; height: 38px; background-color: #fffbeb; color: #d97706 !important;">
                        <i class="mdi mdi-receipt-text-check" style="font-size: 20px;"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="verifyProofModalLabel{{ $order->id }}" style="font-size: 16px;">
                                Verifikasi Bukti Transfer - {{ $order->order_number }}
                            </h6>
                            <span class="badge {{ $order->payment_status == 'Paid' ? 'badge-soft-success' : 'badge-soft-warning' }}" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">
                                {{ $order->payment_status == 'Paid' ? 'Lunas' : 'Menunggu Verifikasi' }}
                            </span>
                        </div>
                        <small class="text-muted d-inline-flex align-items-center gap-1 mt-0.5" style="font-size: 12px;">
                            <span>Pelanggan: <strong class="text-dark">{{ $order->customer_name }}</strong></span>
                            <span>•</span>
                            <span>Total: <strong class="text-success">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong></span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
            </div>

            <!-- Body Modal -->
            <div class="modal-body bg-white" style="padding: 24px;">
                <div class="row g-4 align-items-start">
                    <!-- Kolom Kiri: Pratinjau Bukti Pembayaran -->
                    <div class="col-md-6 text-center">
                        <div class="proof-image-container p-2 border rounded-3 bg-light" style="border-color: #e2e8f0 !important;">
                            @if($order->has_payment_proof && $order->payment_proof_url)
                                <a href="{{ $order->payment_proof_url }}" target="_blank" title="Klik untuk memperbesar bukti transfer">
                                    <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer {{ $order->order_number }}" class="img-fluid rounded-2 shadow-sm" style="max-height: 380px; width: 100%; object-fit: contain; background: #fff;">
                                </a>
                                <div class="mt-2 text-muted" style="font-size: 11.5px;">
                                    <i class="mdi mdi-magnify-plus-outline me-1"></i> Klik gambar untuk melihat ukuran penuh
                                </div>
                            @else
                                <div class="py-5 text-center text-muted">
                                    <i class="mdi mdi-image-off-outline mb-2" style="font-size: 48px; color: #cbd5e1;"></i>
                                    <p class="mb-0 font-weight-500" style="font-size: 13px;">Belum ada berkas bukti transfer yang diunggah.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian & Formulir Verifikasi -->
                    <div class="col-md-6">
                        <!-- Box 1: Informasi Pembayaran -->
                        <div class="p-3 bg-light rounded-3 border mb-3" style="border-color: #e2e8f0 !important;">
                            <div class="text-uppercase text-muted font-weight-bold mb-2 pb-1 border-bottom d-flex align-items-center gap-1.5" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="mdi mdi-information-outline text-primary"></i>
                                <span>Informasi Pembayaran</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: #f1f5f9 !important; font-size: 12.5px;">
                                <span class="text-muted">Metode Bayar:</span>
                                <strong class="text-dark">{{ $order->payment_method ?? 'Transfer Bank' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: #f1f5f9 !important; font-size: 12.5px;">
                                <span class="text-muted">Nominal Transfer:</span>
                                <strong class="text-success" style="font-size: 14px;">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: #f1f5f9 !important; font-size: 12.5px;">
                                <span class="text-muted">Waktu Unggah:</span>
                                <span class="text-dark font-weight-500">{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMM YYYY, HH:mm') . ' WIB' : '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: #f1f5f9 !important; font-size: 12.5px;">
                                <span class="text-muted">Status Pesanan:</span>
                                <span class="text-dark font-weight-500">{{ ucfirst($order->order_status) }}</span>
                            </div>
                            @if($order->catatan_verifikasi_pembayaran)
                                <div class="mt-2 pt-2 border-top" style="font-size: 12px;">
                                    <span class="text-muted d-block mb-1 font-weight-500">Catatan Riwayat Verifikasi:</span>
                                    <div class="p-2 bg-white rounded border text-muted" style="font-size: 11.5px; border-color: #e2e8f0 !important;">
                                        {{ $order->catatan_verifikasi_pembayaran }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($order->payment_status !== 'Paid')
                            <!-- Box 2: Alasan Penolakan Langsung Aktif (Minimalis, Simple, Tanpa Collapse) -->
                            <div class="p-3 rounded-3 border bg-white" style="border-color: #e2e8f0 !important;">
                                <form action="{{ route('orders.reject-proof', $order->id) }}" method="POST" id="formRejectProof{{ $order->id }}">
                                    @csrf
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label text-dark font-weight-bold mb-0" style="font-size: 12px;">
                                            <i class="mdi mdi-alert-circle-outline text-danger me-1"></i> Alasan Penolakan Pembayaran
                                        </label>
                                        <span class="text-muted" style="font-size: 11px;">(Pilih cepat / ketik sendiri)</span>
                                    </div>
                                    
                                    <!-- Preset Alasan Cepat (Pill Chips) -->
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <button type="button" class="btn btn-xs btn-light border text-secondary" onclick="document.getElementById('reasonTextarea{{ $order->id }}').value = 'Nominal transfer tidak sesuai dengan total tagihan.'" style="font-size: 11px; padding: 3px 8px; border-radius: 6px;">Nominal Kurang</button>
                                        <button type="button" class="btn btn-xs btn-light border text-secondary" onclick="document.getElementById('reasonTextarea{{ $order->id }}').value = 'Gambar bukti transfer buram atau tidak terbaca.'" style="font-size: 11px; padding: 3px 8px; border-radius: 6px;">Foto Buram</button>
                                        <button type="button" class="btn btn-xs btn-light border text-secondary" onclick="document.getElementById('reasonTextarea{{ $order->id }}').value = 'Mutasi transfer belum ditemukan pada rekening bank toko.'" style="font-size: 11px; padding: 3px 8px; border-radius: 6px;">Mutasi Belum Masuk</button>
                                        <button type="button" class="btn btn-xs btn-light border text-secondary" onclick="document.getElementById('reasonTextarea{{ $order->id }}').value = 'Nama rekening pengirim tidak sesuai / bukti tidak valid.'" style="font-size: 11px; padding: 3px 8px; border-radius: 6px;">Bukti Tidak Valid</button>
                                    </div>

                                    <textarea id="reasonTextarea{{ $order->id }}" name="alasan_penolakan" class="form-control" rows="2" placeholder="Wajib diisi jika menolak bukti transfer..." style="font-size: 12px; border-radius: 6px; border: 1px solid #cbd5e1; resize: none;"></textarea>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-success d-flex align-items-center p-2 mb-0" style="font-size: 12.5px; border-radius: 8px;">
                                <i class="mdi mdi-check-decagram fs-5 me-2"></i>
                                <span>Pesanan ini telah diverifikasi Lunas dan berstatus <strong>{{ $order->order_status }}</strong>.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Modal (Simple, Minimalis, Tanpa Gradiasi) -->
            <div class="modal-footer border-top bg-light d-flex justify-content-between align-items-center flex-shrink-0" style="padding: 14px 24px;">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" style="padding: 7px 18px; border-radius: 6px; font-weight: 500; font-size: 12.5px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569;">Tutup</button>
                
                <div class="d-flex gap-2">
                    @if($order->payment_status !== 'Paid')
                        <!-- Hidden Form Konfirmasi Lunas -->
                        <form action="{{ route('orders.confirm-proof', $order->id) }}" method="POST" id="formConfirmProof{{ $order->id }}" class="d-none">
                            @csrf
                        </form>

                        <!-- Tombol Tolak Pembayaran (Flat Solid Red) -->
                        <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1.5 shadow-none" onclick="handleRejectProofWithSwal({{ $order->id }}, '{{ $order->order_number }}')" style="padding: 7px 18px; border-radius: 6px; font-weight: 500; font-size: 12.5px; background: #dc2626; border: 1px solid #dc2626; color: #ffffff;">
                            <i class="mdi mdi-close-circle-outline"></i>
                            <span>Tolak Pembayaran</span>
                        </button>

                        <!-- Tombol Konfirmasi Lunas (Flat Solid Green #00b207 - Tanpa Gradiasi) -->
                        <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-1.5 shadow-none" onclick="handleConfirmProofWithSwal({{ $order->id }}, '{{ $order->order_number }}')" style="padding: 7px 20px; border-radius: 6px; font-weight: 500; font-size: 12.5px; background: #00b207; border: 1px solid #00b207; color: #ffffff;">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span>Konfirmasi Lunas</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.handleConfirmProofWithSwal === 'undefined') {
        window.handleConfirmProofWithSwal = function(orderId, orderNumber) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Pembayaran Lunas?',
                    text: `Pesanan ${orderNumber} akan diubah menjadi LUNAS dan statusnya menjadi Dikemas untuk segera diproses.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#00b207',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '<i class="mdi mdi-check-bold me-1"></i> Ya, Konfirmasi Lunas',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`formConfirmProof${orderId}`);
                        if (form) form.submit();
                    }
                });
            } else {
                if (confirm(`Apakah Anda yakin bukti transfer pesanan ${orderNumber} sah dan lunas?`)) {
                    const form = document.getElementById(`formConfirmProof${orderId}`);
                    if (form) form.submit();
                }
            }
        };
    }

    if (typeof window.handleRejectProofWithSwal === 'undefined') {
        window.handleRejectProofWithSwal = function(orderId, orderNumber) {
            const textarea = document.getElementById(`reasonTextarea${orderId}`);
            const reason = textarea ? textarea.value.trim() : '';

            if (!reason) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Alasan Penolakan Wajib Diisi',
                        text: 'Silakan pilih preset alasan cepat atau ketikkan alasan penolakan bukti pembayaran terlebih dahulu.',
                        icon: 'warning',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Mengerti'
                    }).then(() => {
                        if (textarea) textarea.focus();
                    });
                } else {
                    alert('Silakan isi alasan penolakan bukti transfer terlebih dahulu.');
                    if (textarea) textarea.focus();
                }
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tolak Bukti Pembayaran?',
                    html: `Bukti transfer pesanan <b>${orderNumber}</b> akan ditolak dengan alasan:<br><div class="p-2.5 my-2 rounded text-danger text-start" style="background: #fef2f2; border: 1px solid #fecaca; font-size: 13px;"><b>"${reason}"</b></div>Berkas bukti transfer saat ini akan dihapus dan pelanggan diminta mengunggah ulang bukti baru.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '<i class="mdi mdi-close-thick me-1"></i> Ya, Tolak Bukti',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(`formRejectProof${orderId}`);
                        if (form) form.submit();
                    }
                });
            } else {
                if (confirm(`Apakah Anda yakin ingin menolak bukti transfer pesanan ${orderNumber}? Alasan: ${reason}`)) {
                    const form = document.getElementById(`formRejectProof${orderId}`);
                    if (form) form.submit();
                }
            }
        };
    }
</script>
