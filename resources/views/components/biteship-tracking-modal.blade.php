@props(['modalId' => 'biteshipTrackingModal'])

<!-- Biteship Tracking Modal Component -->
<div class="biteship-modal-backdrop" id="{{ $modalId }}" onclick="if(event.target === this) closeBiteshipTrackingModal();">
    <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTrackingTitle">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <span class="visually-hidden">Powered by Biteship</span>
                <h3 id="modalTrackingTitle" class="biteship-modal-title">Pelacakan Pengiriman</h3>
                <div class="biteship-modal-subtitle">
                    Pesanan <strong id="modalOrderNumberDisplay">-</strong> &bull; Resi: <strong id="modalResiCodeDisplay">-</strong>
                </div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeBiteshipTrackingModal()" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="biteship-modal-body">
            <!-- Order & Courier Info Strip -->
            <div class="tracking-summary-strip">
                <div class="summary-item">
                    <span class="label">Kurir Pengiriman</span>
                    <span class="value">
                        <i class="fa-solid fa-motorcycle text-success me-1" id="modalCourierIcon"></i>
                        <span id="modalCourierName">Kurir Toko / Pengiriman Standar</span>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="label">Nomor Resi</span>
                    <span class="value" style="display: flex; align-items: center; gap: 6px;">
                        <span id="modalResiCodeText" style="font-family: monospace; font-size: 12.5px;">-</span>
                        <button type="button" class="btn-copy-resi" id="modalCopyResiBtn" onclick="copyModalResi()" title="Salin resi">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="label">Penerima</span>
                    <span class="value" id="modalRecipientName">-</span>
                </div>
                <div class="summary-item">
                    <span class="label">Status Pengiriman</span>
                    <span class="value" id="modalStatusBadgeContainer">-</span>
                </div>
            </div>

            <!-- Checkpoint Timeline -->
            <div class="tracking-timeline-container">
                <h4 class="timeline-heading"><i class="fa-solid fa-route me-2 text-success"></i> Status Perjalanan Paket</h4>
                <div class="tracking-timeline" id="modalTimelineContainer">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>

        <div class="biteship-modal-footer">
            <div class="biteship-modal-footer-left">
                <button type="button" class="btn-modal-close" onclick="closeBiteshipTrackingModal()">Tutup</button>
                <a href="#" id="modalDetailLink" class="btn-modal-detail">
                    <i class="fa-solid fa-file-invoice me-1"></i> Rincian Pesanan
                </a>
            </div>
            <div class="biteship-modal-footer-right">
                <a href="#" id="modalWaLink" target="_blank" class="btn-modal-wa">
                    <i class="fa-brands fa-whatsapp me-1"></i> Hubungi CS Toko
                </a>
            </div>
        </div>
    </div>
</div>
