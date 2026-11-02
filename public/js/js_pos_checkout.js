let cart = {};

    function loadCartFromStorage() {
        try {
            const savedCart = localStorage.getItem('zyh_pos_cart');
            if (savedCart) {
                cart = JSON.parse(savedCart);
            }
        } catch (e) {
            console.error("Gagal memuat keranjang POS:", e);
            cart = {};
        }
    }

    function saveCartToStorage() {
        try {
            localStorage.setItem('zyh_pos_cart', JSON.stringify(cart));
        } catch (e) {
            console.error("Gagal menyimpan keranjang POS:", e);
        }
    }

    function addToCart(id, name, price, image, maxStock, originalPrice) {
        if(cart[id]) {
            if(maxStock && cart[id].quantity >= maxStock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Terbatas',
                    text: `Maksimum stok ${name} hanya tersedia ${maxStock} item.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }
            cart[id].quantity += 1;
            if (originalPrice && !cart[id].originalPrice) {
                cart[id].originalPrice = originalPrice;
            }
        } else {
            cart[id] = {
                name: name,
                price: price,
                originalPrice: originalPrice || price,
                image: image,
                quantity: 1,
                maxStock: maxStock
            };
        }
        saveCartToStorage();
        renderCheckout();
    }

    function removeFromCart(id) {
        if(cart[id]) {
            cart[id].quantity -= 1;
            if(cart[id].quantity <= 0) {
                delete cart[id];
            }
            saveCartToStorage();
            renderCheckout();
        }
    }

    function renderCheckout() {
        let html = '';
        let breakdownHtml = '';
        let totalOriginalPrice = 0;
        let totalDiscount = 0;
        let totalFinalPrice = 0;
        let distinctCount = Object.keys(cart).length;

        const footerEl = document.getElementById('checkout-table-footer');
        const breakdownEl = document.getElementById('checkout-product-breakdown');
        const totalOrigEl = document.getElementById('checkout-total-original');
        const totalDiscEl = document.getElementById('checkout-total-discount');
        const totalEl = document.getElementById('checkout-total');
        const subtotalEl = document.getElementById('checkout-subtotal');

        if (distinctCount === 0) {
            if (footerEl) footerEl.style.display = 'none';
            html = `
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="mdi mdi-cart-off mb-2 text-muted" style="font-size: 48px; display: block;"></i>
                        <p class="mb-3" style="font-size: 14px;">Keranjang belanja kosong</p>
                        <a href=" + ((window.posCheckoutConfig && window.posCheckoutConfig.posIndexRoute) || '/admin/pos') + " class="btn btn-sm btn-primary px-3 shadow-sm" style="border-radius: 6px;">
                            <i class="mdi mdi-arrow-left me-1"></i> Pilih Produk di Kasir
                        </a>
                    </td>
                </tr>
            `;
            document.getElementById('checkout-items').innerHTML = html;
            if (breakdownEl) breakdownEl.innerHTML = '<p class="text-muted small mb-0 text-center py-2">Belum ada rincian produk.</p>';
            if (totalOrigEl) totalOrigEl.innerText = 'Rp. 0';
            if (totalDiscEl) {
                totalDiscEl.innerText = 'Rp. 0';
                totalDiscEl.className = 'text-muted';
            }
            if (totalEl) totalEl.innerText = 'Rp. 0';
            if (subtotalEl) subtotalEl.innerText = 'Rp. 0';
            return;
        }

        if (footerEl) footerEl.style.display = 'flex';

        for (let id in cart) {
            let item = cart[id];
            let origPrice = parseFloat(item.originalPrice) || parseFloat(item.price);
            let finalPrice = parseFloat(item.price);
            let itemOriginalTotal = origPrice * item.quantity;
            let itemFinalTotal = finalPrice * item.quantity;
            let itemDiscountTotal = itemOriginalTotal - itemFinalTotal;
            if (itemDiscountTotal < 0) itemDiscountTotal = 0;

            totalOriginalPrice += itemOriginalTotal;
            totalDiscount += itemDiscountTotal;
            totalFinalPrice += itemFinalTotal;

            let discountPercent = (origPrice > 0 && itemDiscountTotal > 0) 
                ? Math.round(((origPrice - finalPrice) / origPrice) * 100) 
                : 0;

            html += `
                <tr class="border-bottom">
                    <td class="align-middle py-4 ps-4">
                        <div class="d-flex align-items-center">
                            <img src="${item.image}" alt="${item.name}" class="rounded flex-shrink-0" style="width: 70px; height: 70px; object-fit: cover;">
                            <div class="ms-3">
                                <span class="d-block text-dark font-weight-500" style="font-size: 14px;">${item.name}</span>
                                ${itemDiscountTotal > 0 ? `<span class="badge text-danger mt-1" style="font-size: 10px; padding: 2px 6px; border-radius: 4px; background: #fee2e2; border: 1px solid #fca5a5;">Diskon ${discountPercent}%</span>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="align-middle font-weight-bold" style="font-size: 14px;">
                        <span>Rp. ${finalPrice.toLocaleString('id-ID')}</span>
                        ${itemDiscountTotal > 0 ? `<br><small class="text-muted text-decoration-line-through fw-normal" style="font-size: 11px;">Rp. ${origPrice.toLocaleString('id-ID')}</small>` : ''}
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-inline-flex align-items-center rounded-pill border px-2 py-1" style="background: #f8f9fa;">
                            <button class="btn p-0 text-muted" onclick="removeFromCart(${id});" style="border:none; background:transparent; line-height: 1;" title="Kurangi">
                                <i class="mdi mdi-minus"></i>
                            </button>
                            <input type="text" class="form-control text-center border-0 p-0 mx-2 bg-transparent" value="${item.quantity}" readonly style="width: 30px; font-size: 14px;">
                            <button class="btn p-0 text-muted" onclick="addToCart(${id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}', ${item.maxStock || 999}, ${origPrice});" style="border:none; background:transparent; line-height: 1;" title="Tambah">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td class="align-middle font-weight-bold pe-4 text-end" style="font-size: 14px;">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span>Rp. ${itemFinalTotal.toLocaleString('id-ID')}</span>
                            <button class="btn btn-sm text-muted p-0" onclick="delete cart[${id}]; saveCartToStorage(); renderCheckout();" style="border:none; background:transparent; line-height: 1;" title="Hapus">
                                <i class="mdi mdi-close-circle-outline" style="font-size: 20px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;

            breakdownHtml += `
                <div class="p-2 px-3 mb-2 rounded bg-light border" style="font-size: 12px; border-color: #f1f5f9 !important;">
                    <div class="d-flex justify-content-between font-weight-bold text-dark mb-1">
                        <span class="text-truncate me-2" title="${item.name}">${item.name} (${item.quantity}x)</span>
                        <span class="text-nowrap">Rp. ${itemFinalTotal.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                        <span>Harga Asli:</span>
                        <span>Rp. ${itemOriginalTotal.toLocaleString('id-ID')}</span>
                    </div>
                    ${itemDiscountTotal > 0 ? `
                    <div class="d-flex justify-content-between text-danger" style="font-size: 11px;">
                        <span>Diskon (${discountPercent}%):</span>
                        <span>-Rp. ${itemDiscountTotal.toLocaleString('id-ID')}</span>
                    </div>
                    ` : `
                    <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                        <span>Diskon:</span>
                        <span>Rp. 0</span>
                    </div>
                    `}
                    <div class="d-flex justify-content-between text-primary font-weight-500 pt-1 border-top mt-1" style="font-size: 11px;">
                        <span>Setelah Diskon:</span>
                        <span>Rp. ${itemFinalTotal.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
        }

        document.getElementById('checkout-items').innerHTML = html;
        if (breakdownEl) breakdownEl.innerHTML = breakdownHtml;

        if (totalOrigEl) totalOrigEl.innerText = 'Rp. ' + totalOriginalPrice.toLocaleString('id-ID');
        if (totalDiscEl) {
            if (totalDiscount > 0) {
                totalDiscEl.innerText = '-Rp. ' + totalDiscount.toLocaleString('id-ID');
                totalDiscEl.className = 'text-danger font-weight-bold';
            } else {
                totalDiscEl.innerText = 'Rp. 0';
                totalDiscEl.className = 'text-muted';
            }
        }

        let finalFormatted = 'Rp. ' + totalFinalPrice.toLocaleString('id-ID');
        if (totalEl) totalEl.innerText = finalFormatted;
        if (subtotalEl) subtotalEl.innerText = finalFormatted;
    }

    function processCheckout() {
        if(Object.keys(cart).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        let paymentMethod = document.getElementById('payment-method').value;
        let customerName = document.getElementById('customer-name').value || 'Walk-in Customer';

        Swal.fire({
            title: 'Memproses Transaksi',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let payload = {
            _token: (window.posCheckoutConfig && window.posCheckoutConfig.csrfToken) || '',
            items: cart,
            payment_method: paymentMethod,
            customer_name: customerName
        };

        fetch((window.posCheckoutConfig && window.posCheckoutConfig.checkoutPostRoute) || '/admin/pos/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            return response.json().then(data => ({
                status: response.status,
                ok: response.ok,
                data: data
            }));
        })
        .then(res => {
            if(res.ok && res.data.success) {
                const orderId = res.data.order ? res.data.order.id_pesanan : null;
                const orderNumber = res.data.order ? res.data.order.order_number : '';

                Swal.fire({
                    icon: 'success',
                    title: 'Checkout Berhasil!',
                    text: res.data.message || 'Transaksi telah berhasil disimpan.',
                    showCancelButton: orderId ? true : false,
                    confirmButtonText: 'Transaksi Baru',
                    cancelButtonText: 'Cetak Invoice',
                    confirmButtonColor: '#00b207',
                    cancelButtonColor: '#7c3aed'
                }).then((result) => {
                    cart = {};
                    localStorage.removeItem('zyh_pos_cart');
                    document.getElementById('customer-name').value = '';

                    if (result.dismiss === Swal.DismissReason.cancel && orderId) {
                        window.open(`/admin/orders/${orderId}/invoice`, '_blank');
                    }
                    window.location.href = (window.posCheckoutConfig && window.posCheckoutConfig.posIndexRoute) || '/admin/pos';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Checkout Gagal!',
                    text: res.data.message || 'Terjadi kesalahan saat memproses transaksi.',
                    confirmButtonColor: '#ea4b48'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: error.message || 'Gagal terhubung ke server.',
                confirmButtonColor: '#ea4b48'
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        loadCartFromStorage();
        renderCheckout();
    });
