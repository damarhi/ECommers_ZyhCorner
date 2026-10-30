/**
 * Zyh Corner - Customer Pages Common & Page-Specific Scripts
 */

// Initialize logged-in state from configuration
window.isLoggedIn = window.layoutConfig?.isLoggedIn || false;

// --- Product Modal Logic ---
window.openProductModal = function(btn) {
    const data = btn.dataset;

    const pmName = document.getElementById('pmName');
    const pmEyebrow = document.getElementById('pmEyebrow');
    const pmQtyInput = document.getElementById('pmQtyInput');
    const pmNewPrice = document.getElementById('pmNewPrice');
    const pmOldPrice = document.getElementById('pmOldPrice');
    const pmDiscountBadge = document.getElementById('pmDiscountBadge');
    const pmStock = document.getElementById('pmStock');
    const pmDesc = document.getElementById('pmDesc');
    const pmCategory = document.getElementById('pmCategory');
    const pmTag = document.getElementById('pmTag');
    const pmSku = document.getElementById('pmSku');
    const pmCartBtn = document.getElementById('pmCartBtn');
    const pmBuyBtn = document.getElementById('pmBuyBtn');
    const pmMainImage = document.getElementById('pmMainImage');
    const thumbContainer = document.getElementById('pmThumbnails');
    const thumbWrapper = document.getElementById('pmThumbnailsContainer');
    const thumbPrev = document.getElementById('pmThumbPrev');
    const thumbNext = document.getElementById('pmThumbNext');

    const pmStockWarning = document.getElementById('pmStockWarning');
    if (pmStockWarning) pmStockWarning.style.display = 'none';
    const pmActions = document.querySelector('.pm-actions');
    if (pmActions) pmActions.classList.remove('is-out-of-stock');
    if (pmName) pmName.textContent = data.name;
    if (pmEyebrow) pmEyebrow.textContent = data.cat || 'Kue & Bakery';
    if (pmQtyInput) pmQtyInput.value = 1;
    let price = parseInt(data.price);
    if (pmNewPrice) {
        pmNewPrice.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price);
    }

    if (data.sale === '1' && data.oldPrice) {
        if (pmOldPrice) {
            pmOldPrice.style.display = 'inline-block';
            pmOldPrice.textContent = data.oldPrice.startsWith('Rp') ? data.oldPrice : ('Rp. ' + new Intl.NumberFormat('id-ID').format(parseInt(data.oldPrice)));
        }
        if (pmDiscountBadge) {
            pmDiscountBadge.style.display = 'inline-block';
            pmDiscountBadge.textContent = data.discountBadge || 'Promo';
        }
    } else {
        if (pmOldPrice) pmOldPrice.style.display = 'none';
        if (pmDiscountBadge) pmDiscountBadge.style.display = 'none';
    }

    const isCustom = data.custom === '1';
    window.currentModalProductStock = (data.stock !== undefined && data.stock !== null && !isNaN(parseInt(data.stock)))
        ? parseInt(data.stock)
        : (window.layoutConfig?.productsStockMap?.[data.id] !== undefined ? parseInt(window.layoutConfig.productsStockMap[data.id]) : 10);
    window.currentModalIsCustom = isCustom;
    if (pmCartBtn) {
        pmCartBtn.style.display = isCustom ? 'none' : '';
    }
    const currentStock = window.currentModalProductStock;
    if (pmStock) {
        if (currentStock <= 0 && !isCustom) {
            pmStock.className = 'pm-stock-pill out';
            pmStock.innerHTML = '<span class="pm-status-dot"></span> <span class="pm-stock-text">Stok Habis</span>';
            if (pmActions) {
                pmActions.classList.add('is-out-of-stock');
            }

            if (pmBuyBtn) {
                pmBuyBtn.style.backgroundColor = '';
                pmBuyBtn.classList.add('disabled');
                pmBuyBtn.disabled = true;
                pmBuyBtn.innerHTML = 'Stok Habis <i class="fa-solid fa-ban"></i>';
                pmBuyBtn.onclick = null;
            }
            if (pmCartBtn) {
                pmCartBtn.style.display = 'none';
                pmCartBtn.classList.add('disabled');
                pmCartBtn.disabled = true;
                pmCartBtn.onclick = null;
            }
            const pmQtyContainer = document.querySelector('.pm-qty');
            if (pmQtyContainer) {
                pmQtyContainer.style.opacity = '0.5';
                pmQtyContainer.style.pointerEvents = 'none';
            }
            const pmQtyInputVal = document.getElementById('pmQtyInput');
            if (pmQtyInputVal) {
                pmQtyInputVal.value = 0;
                pmQtyInputVal.disabled = true;
            }
        } else {
            if (pmActions) {
                pmActions.classList.remove('is-out-of-stock');
            }
            const pmQtyContainer = document.querySelector('.pm-qty');
            if (pmQtyContainer) {
                pmQtyContainer.style.opacity = '';
                pmQtyContainer.style.pointerEvents = '';
            }
            const pmQtyInputVal = document.getElementById('pmQtyInput');
            if (pmQtyInputVal) {
                pmQtyInputVal.disabled = false;
                if (parseInt(pmQtyInputVal.value) <= 0) {
                    pmQtyInputVal.value = 1;
                }
            }
            if (pmCartBtn) {
                pmCartBtn.style.display = isCustom ? 'none' : 'inline-flex';
                pmCartBtn.classList.remove('disabled');
                pmCartBtn.disabled = false;
            }
            if (isCustom) {
                pmStock.className = 'pm-stock-pill custom';
                pmStock.innerHTML = '<span class="pm-status-dot"></span> <span class="pm-stock-text">Pre-Order / Dibuat Sesuai Pesanan</span>';
            } else if (currentStock <= 3) {
                pmStock.className = 'pm-stock-pill low';
                pmStock.innerHTML = `<span class="pm-status-dot"></span> <span class="pm-stock-text">Sisa ${currentStock} unit lagi (Segera Habis!)</span>`;
            } else {
                pmStock.className = 'pm-stock-pill in';
                pmStock.innerHTML = `<span class="pm-status-dot"></span> <span class="pm-stock-text">Tersedia: ${currentStock} unit</span>`;
            }
            if (pmBuyBtn) {
                pmBuyBtn.classList.remove('disabled');
                pmBuyBtn.disabled = false;
                if (isCustom) {
                    pmBuyBtn.style.backgroundColor = '#25d366';
                    pmBuyBtn.innerHTML = 'Pesan via WhatsApp <i class="fa-brands fa-whatsapp"></i>';
                    pmBuyBtn.onclick = function() {
                        const qty = parseInt(document.getElementById('pmQtyInput')?.value || 1);
                        const formattedPrice = new Intl.NumberFormat('id-ID').format(price);
                        const sName = window.storeName || "Zyh Corner";
                        const sPhone = window.storePhone || "628123456789";
                        const message = encodeURIComponent("Halo " + sName + ", saya tertarik untuk memesan Custom Product: " + data.name + " (Harga: Rp. " + formattedPrice + ") sebanyak " + qty + " pcs. Mohon info lebih lanjut.");
                        window.open("https://wa.me/" + sPhone + "?text=" + message, "_blank");
                    };
                } else {
                    pmBuyBtn.style.backgroundColor = '';
                    pmBuyBtn.innerHTML = 'Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>';
                    pmBuyBtn.onclick = function() {
                        if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
                            window.closeProductModal();
                            window.showAdminShoppingNotice();
                            return;
                        }
                        if (!window.isLoggedIn) {
                            window.closeProductModal();
                            window.location.href = window.layoutConfig?.loginRoute || "/login";
                            return;
                        }
                        window.addToCart({
                            id: data.id,
                            name: data.name,
                            price: price,
                            img: data.f1 || '',
                            qty: parseInt(document.getElementById('pmQtyInput').value)
                        }, false);
                        window.closeProductModal();
                        window.location.href = window.layoutConfig?.cartRoute || "/cart";
                    };
                }
            }
            if (pmCartBtn) {
                pmCartBtn.classList.remove('disabled');
                pmCartBtn.disabled = false;
                pmCartBtn.onclick = function() {
                    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
                        window.closeProductModal();
                        window.showAdminShoppingNotice();
                        return;
                    }
                    window.addToCart({
                        id: data.id,
                        name: data.name,
                        price: price,
                        img: data.f1 || '',
                        qty: parseInt(document.getElementById('pmQtyInput').value)
                    });
                    window.closeProductModal();
                };
            }
        }
    }

    if (pmDesc) {
        pmDesc.textContent = data.desc ||
            'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla nibh diam, blandit vel consequat nec, ultrices et ipsum. Nulla varius magna a consequat pulvinar.';
    }
    if (pmCategory) pmCategory.textContent = data.cat || '-';

    // Generate modern tag chips
    if (pmTag) {
        pmTag.innerHTML = '';
        const rawTags = [
            data.name ? data.name.split(' ')[0] : null,
            'Kue Pilihan',
            'Fresh Daily',
            'Bahan Premium'
        ].filter(Boolean);
        const uniqueTags = [...new Set(rawTags)];
        uniqueTags.forEach(tagName => {
            const chip = document.createElement('span');
            chip.className = 'pm-chip';
            chip.textContent = tagName;
            pmTag.appendChild(chip);
        });
    }

    if (pmSku) pmSku.textContent = 'ZYH-' + String(data.id || '1').padStart(4, '0');

    // Handle gallery & thumbnails
    if (thumbContainer) {
        thumbContainer.innerHTML = '';
        const images = [];
        if (data.f1) images.push(data.f1);
        if (data.f2) images.push(data.f2);
        if (data.f3) images.push(data.f3);
        if (data.f4) images.push(data.f4);

        // All image data comes from database

        if (pmMainImage) pmMainImage.src = images[0];

        if (images.length <= 1) {
            if (thumbWrapper) thumbWrapper.style.display = 'none';
        } else {
            if (thumbWrapper) thumbWrapper.style.display = 'flex';
            if (thumbPrev) thumbPrev.style.display = images.length > 3 ? 'flex' : 'none';
            if (thumbNext) thumbNext.style.display = images.length > 3 ? 'flex' : 'none';

            images.forEach((imgSrc, index) => {
                const div = document.createElement('div');
                div.className = 'pm-thumb' + (index === 0 ? ' active' : '');
                div.innerHTML = '<img src="' + imgSrc + '" alt="Thumbnail">';
                div.onclick = function() {
                    document.querySelectorAll('.pm-thumb').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    if (pmMainImage) pmMainImage.src = imgSrc;
                };
                thumbContainer.appendChild(div);
            });
        }
    }
    const modal = document.getElementById('productModal');
    if (modal) modal.style.display = 'flex';
};

window.updateModalQty = function(delta) {
    const input = document.getElementById('pmQtyInput');
    if (input) {
        let currentVal = parseInt(input.value);
        if (isNaN(currentVal) || currentVal < 1) currentVal = 1;
        let val = currentVal + delta;
        if (val < 1) val = 1;

        const maxStock = window.currentModalProductStock;
        const warningEl = document.getElementById('pmStockWarning');
        if (!window.currentModalIsCustom && maxStock !== undefined && maxStock !== null && !isNaN(maxStock)) {
            if (val >= maxStock) {
                val = maxStock;
                if (warningEl) {
                    warningEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maksimal stok produk tercapai (${maxStock} unit)`;
                    warningEl.style.display = 'block';
                }
            } else {
                if (warningEl) warningEl.style.display = 'none';
            }
        } else {
            if (warningEl) warningEl.style.display = 'none';
        }
        input.value = val;
    }
};

window.scrollModalThumbs = function(direction) {
    const container = document.getElementById('pmThumbnails');
    if (container) {
        container.scrollBy({ top: direction * 80, behavior: 'smooth' });
    }
};

window.closeProductModal = function() {
    const modal = document.getElementById('productModal');
    if (modal) modal.style.display = 'none';
};

// Close modal on click outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        window.closeProductModal();
    }
});

// --- Mobile Menu Toggle ---
window.toggleMobileMenu = function(show) {
    const drawer = document.getElementById('mobileNavDrawer');
    const overlay = document.getElementById('mobileNavOverlay');
    if (!drawer || !overlay) return;

    if (show) {
        overlay.style.display = 'block';
        setTimeout(() => {
            overlay.style.opacity = '1';
            drawer.classList.add('open');
        }, 10);
    } else {
        drawer.classList.remove('open');
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
};

// --- Shopping Cart Logic ---
window.cart = JSON.parse(localStorage.getItem('zyh_cart')) || [];

window.toggleCart = function(show) {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    if (!sidebar || !overlay) return;

    if (show) {
        overlay.style.display = 'block';
        setTimeout(() => {
            overlay.style.opacity = '1';
            sidebar.classList.add('open');
        }, 10);
        window.renderCart();
    } else {
        sidebar.classList.remove('open');
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
};
window.showAdminShoppingNotice = function() {
    const role = window.layoutConfig?.userRole || '';
    const roleLabel = role === 'admin' ? 'Administrator' : 'Kasir Toko';
    const posRoute = window.layoutConfig?.posRoute || '/admin/pos';

    if (window.Swal) {
        Swal.fire({
            icon: 'info',
            title: 'Akses Belanja Khusus Pelanggan',
            html: `Akun Anda terdaftar sebagai <b>${roleLabel}</b>.<br><br>Fitur keranjang belanja dan checkout online hanya diperuntukkan bagi pelanggan toko.<br>Untuk memproses transaksi penjualan langsung di toko, silakan gunakan menu <b>POS Kasir</b>.`,
            confirmButtonText: '<i class="fa-solid fa-cash-register me-1"></i> Buka POS Kasir',
            confirmButtonColor: '#024220',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = posRoute;
            }
        });
    } else {
        if (confirm(`Akses Belanja Khusus Pelanggan.\n\nAkun Anda terdaftar sebagai ${roleLabel}.\nFitur keranjang belanja dan checkout online hanya diperuntukkan bagi pelanggan toko.\n\nApakah Anda ingin membuka menu POS Kasir?`)) {
            window.location.href = posRoute;
        }
    }
};

window.addToCart = function(product, openDrawer = true) {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }

    const availableStock = window.layoutConfig?.productsStockMap?.[product.id] ?? window.detailConfig?.productStock;
    const requestedQty = product.qty || 1;
    const existing = window.cart.find(item => item.id === product.id);
    const currentQtyInCart = existing ? existing.qty : 0;
    const totalDesiredQty = currentQtyInCart + requestedQty;

    if (availableStock !== undefined && availableStock !== null && !isNaN(availableStock)) {
        const stockLimit = parseInt(availableStock);
        if (stockLimit <= 0) {
            const qtyWarn = document.getElementById('qtyStockWarning');
            if (qtyWarn) {
                qtyWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Stok produk ini sedang habis`;
                qtyWarn.style.display = 'block';
            }
            const pmWarn = document.getElementById('pmStockWarning');
            if (pmWarn) {
                pmWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Stok habis`;
                pmWarn.style.display = 'block';
            }
            if (!qtyWarn && !pmWarn && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Habis',
                    text: `Maaf, produk '${product.name}' saat ini sedang habis.`,
                    confirmButtonColor: '#00b207'
                });
            }
            return false;
        }
        if (totalDesiredQty > stockLimit) {
            const qtyWarn = document.getElementById('qtyStockWarning');
            if (qtyWarn) {
                qtyWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa ${stockLimit} unit)`;
                qtyWarn.style.display = 'block';
            }
            const pmWarn = document.getElementById('pmStockWarning');
            if (pmWarn) {
                pmWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maksimal stok tercapai (${stockLimit} unit)`;
                pmWarn.style.display = 'block';
            }
            if (!qtyWarn && !pmWarn && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Stok Tercapai',
                    text: `Maksimal stok yang tersedia untuk '${product.name}' adalah ${stockLimit} unit. Anda sudah memiliki ${currentQtyInCart} unit di keranjang.`,
                    confirmButtonColor: '#00b207'
                });
            }
            return false;
        }
    }
    if (existing) {
        existing.qty += requestedQty;
        if (product.bisa_custom_teks !== undefined) {
            existing.bisa_custom_teks = product.bisa_custom_teks;
        }
    } else {
        window.cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            img: product.img,
            qty: requestedQty,
            bisa_custom_teks: product.bisa_custom_teks || false,
            custom_text: product.custom_text || ''
        });
    }
    window.saveCart();
    if (openDrawer) {
        window.toggleCart(true);
    }
    return true;
};

window.updateQty = function(id, delta) {
    const item = window.cart.find(i => i.id === id);
    if (item) {
        if (delta > 0) {
            const availableStock = window.layoutConfig?.productsStockMap?.[id];
            if (availableStock !== undefined && availableStock !== null && !isNaN(availableStock)) {
                const stockLimit = parseInt(availableStock);
                if (item.qty + delta > stockLimit) {
                    const cartWarn = document.getElementById('cartQtyWarning_' + id);
                    if (cartWarn) {
                        cartWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maks. ${stockLimit} unit`;
                        cartWarn.style.display = 'inline-block';
                    }
                    const drawerWarn = document.getElementById('drawerQtyWarning_' + id);
                    if (drawerWarn) {
                        drawerWarn.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maks. ${stockLimit} unit`;
                        drawerWarn.style.display = 'inline-block';
                    }
                    return;
                }
            }
        }

        item.qty += delta;
        if (item.qty < 1) {
            window.removeFromCart(id);
        } else {
            window.saveCart();
            window.renderCart();
            const cartContainer = document.getElementById('cartPageContainer');
            if (cartContainer) {
                const subtotal = window.cart.reduce((total, i) => total + (i.price * i.qty), 0);
                renderCartPage(cartContainer, window.cart, subtotal);
            }
        }
    }
};
window.removeFromCart = function(id) {
    window.cart = window.cart.filter(item => item.id !== id);
    window.saveCart();
    window.renderCart();
};

window.saveCart = function() {
    localStorage.setItem('zyh_cart', JSON.stringify(window.cart));
    window.updateHeaderCart();
};

window.updateHeaderCart = function() {
    const count = window.cart.reduce((total, item) => total + item.qty, 0);
    const subtotal = window.cart.reduce((total, item) => total + (item.price * item.qty), 0);

    const badge = document.getElementById('cartBadge');
    const cartCount = document.getElementById('cartCount');
    const headerSubtotal = document.getElementById('cartSubtotalHeader');
    const cartSubtotal = document.getElementById('cartSubtotal');

    if (badge) badge.textContent = count;
    if (cartCount) cartCount.textContent = count;
    if (headerSubtotal) {
        headerSubtotal.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
    }
    if (cartSubtotal) {
        cartSubtotal.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
    }

    // Dynamic rendering of cart items on userChart/checkout summary page if they exist
    const cartPageContent = document.getElementById('cartPageContent');
    if (cartPageContent) {
        renderCartPage(cartPageContent, window.cart, subtotal);
    }

    const orderItemsSummary = document.getElementById('orderItemsSummary');
    if (orderItemsSummary) {
        renderOrderSummary(orderItemsSummary, window.cart, subtotal);
    }
};

window.renderCart = function() {
    const list = document.getElementById('cartItemsList');
    if (!list) return;

    if (window.cart.length === 0) {
        list.innerHTML = `
            <div class="empty-cart-msg">
                <i class="fa-solid fa-bag-shopping"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        return;
    }

    list.innerHTML = window.cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-img">
                <img src="${item.img}" alt="${item.name}">
            </div>
            <div class="cart-item-info">
                <span class="cart-item-name">${item.name}</span>
                <div class="cart-item-price">
                    ${item.qty} x Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}
                </div>
                <div class="cart-item-qty-container" style="display: flex; flex-direction: column; align-items: flex-start;">
                    <div class="cart-item-qty-wrap">
                        <button class="cart-qty-btn" onclick="window.updateQty('${item.id}', -1)"><i class="fa-solid fa-minus"></i></button>
                        <span>${item.qty}</span>
                        <button class="cart-qty-btn" onclick="window.updateQty('${item.id}', 1)"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <span id="drawerQtyWarning_${item.id}" class="drawer-stock-warning" style="display: none; color: #dc2626; font-size: 10.5px; font-weight: 500; margin-top: 3px; line-height: 1.2;">
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maks. stok
                    </span>
                </div>
            </div>
            <div class="cart-item-remove" onclick="window.removeFromCart('${item.id}')">
                <i class="fa-solid fa-xmark"></i>
            </div>
        </div>
    `).join('');

    window.updateHeaderCart();
};

// Profile Dropdown Logic
document.addEventListener('DOMContentLoaded', function() {
    const profileWrapper = document.getElementById('profileDropdownWrapper');
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');

    if (profileWrapper && profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const show = profileMenu.classList.toggle('show');
            if (show) {
                profileWrapper.classList.add('active');
            } else {
                profileWrapper.classList.remove('active');
            }
        });

        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
                profileWrapper.classList.remove('active');
            }
        });
    }

    // Success notification handler
    if (window.layoutConfig?.successMessage) {
        Swal.fire({
            title: 'Berhasil!',
            text: window.layoutConfig.successMessage,
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: {
                popup: 'rounded-4'
            }
        });
    }

    // Run header cart update on page load
    window.updateHeaderCart();
});

// --- Confirm Logout Alert ---
window.confirmLogout = function() {
    Swal.fire({
        title: 'Konfirmasi Keluar',
        text: 'Apakah Anda yakin ingin keluar dari akun Anda?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00b207',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                localStorage.removeItem('zyh_cust_name');
                localStorage.removeItem('zyh_cust_address');
                localStorage.removeItem('zyh_cust_email');
                localStorage.removeItem('zyh_cust_phone');
            } catch (e) {}
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) logoutForm.submit();
        }
    });
};


// ==========================================================================
// DASHBOARD & SETTINGS PAGE SPECIFIC LOGIC
// ==========================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Load and Sync dashboard inputs with database values
    if (window.dashboardConfig) {
        const conf = window.dashboardConfig;
        const storedName = conf.userNama;
        const rawAddress = conf.userAddress || conf.userAlamat || "";
        const storedAddress = rawAddress || "Belum ada alamat tagihan (Klik Edit Address)";
        const storedEmail = conf.userEmail;
        const storedPhone = conf.userPhone ? "(+62) " + conf.userPhone : "Nomor HP belum diatur";

        const displayProfileName = document.getElementById('displayProfileName');
        const displayBillingName = document.getElementById('displayBillingName');
        const displayBillingAddress = document.getElementById('displayBillingAddress');
        const displayBillingEmail = document.getElementById('displayBillingEmail');
        const displayBillingPhone = document.getElementById('displayBillingPhone');

        const inputProfileName = document.getElementById('inputProfileName');
        const inputBillingAddress = document.getElementById('inputBillingAddress');
        const inputBillingEmail = document.getElementById('inputBillingEmail');
        const inputBillingPhone = document.getElementById('inputBillingPhone');

        if (displayProfileName) displayProfileName.textContent = storedName;
        if (displayBillingName) displayBillingName.textContent = storedName;
        if (displayBillingAddress) {
            displayBillingAddress.textContent = storedAddress;
            if (!rawAddress) {
                displayBillingAddress.style.color = '#888';
                displayBillingAddress.style.fontStyle = 'italic';
            } else {
                displayBillingAddress.style.color = '';
                displayBillingAddress.style.fontStyle = '';
            }
        }
        if (displayBillingEmail) displayBillingEmail.textContent = storedEmail;
        if (displayBillingPhone) displayBillingPhone.textContent = storedPhone;

        if (inputProfileName) inputProfileName.value = storedName;
        if (inputBillingAddress) inputBillingAddress.value = rawAddress;
        if (inputBillingEmail) inputBillingEmail.value = storedEmail;
        if (inputBillingPhone) inputBillingPhone.value = conf.userPhone || "";
    }

    // Load and Sync settings inputs with database values
    if (window.settingsConfig) {
        const conf = window.settingsConfig;
        const storedName = conf.userNama;
        const storedAddress = conf.userAddress || "";
        const storedEmail = conf.userEmail;
        const storedPhone = conf.userPhone || "";

        const names = storedName.split(' ');
        const billFirstName = names[0] || '';
        const billLastName = names.slice(1).join(' ') || '';

        const inputBillingFirstName = document.getElementById('inputBillingFirstName');
        const inputBillingLastName = document.getElementById('inputBillingLastName');
        const inputBillingStreet = document.getElementById('inputBillingStreet');
        const inputBillingEmail = document.getElementById('inputBillingEmail');
        const inputBillingPhone = document.getElementById('inputBillingPhone');

        if (inputBillingFirstName) inputBillingFirstName.value = billFirstName;
        if (inputBillingLastName) inputBillingLastName.value = billLastName;
        if (inputBillingStreet) inputBillingStreet.value = storedAddress;
        if (inputBillingEmail) inputBillingEmail.value = storedEmail;
        if (inputBillingPhone) inputBillingPhone.value = storedPhone;

        const inputBillingCompany = document.getElementById('inputBillingCompany');
        const inputBillingDescribe = document.getElementById('inputBillingDescribe');

        if (inputBillingCompany) inputBillingCompany.value = "";
        if (inputBillingDescribe) inputBillingDescribe.value = "";
    }
});

// Profile and Address Modals
window.openEditProfile = function() {
    const modal = document.getElementById('modalEditProfile');
    if (modal) modal.style.display = 'flex';
};

window.closeEditProfile = function() {
    const modal = document.getElementById('modalEditProfile');
    if (modal) modal.style.display = 'none';
};

window.saveProfile = function(e) {
    e.preventDefault();
    const inputProfileName = document.getElementById('inputProfileName');
    if (!inputProfileName) return;

    const newName = inputProfileName.value;

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(window.dashboardConfig?.loginRoute ? '/settings/account' : window.settingsConfig.settingsAccountRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.dashboardConfig?.csrfToken || window.settingsConfig?.csrfToken
        },
        body: JSON.stringify({
            nama: newName,
            email: window.dashboardConfig?.userEmail || window.settingsConfig?.userEmail,
            no_hp: window.dashboardConfig?.userPhone || window.settingsConfig?.userPhone
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            const displayProfileName = document.getElementById('displayProfileName');
            const displayBillingName = document.getElementById('displayBillingName');

            if (displayProfileName) displayProfileName.textContent = newName;
            if (displayBillingName) displayBillingName.textContent = newName;

            window.closeEditProfile();

            Swal.fire({
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui.',
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            }).then(() => {
                window.location.reload();
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
    });
};

window.openEditAddress = function() {
    const modal = document.getElementById('modalEditAddress');
    if (modal) modal.style.display = 'flex';
};

window.closeEditAddress = function() {
    const modal = document.getElementById('modalEditAddress');
    if (modal) modal.style.display = 'none';
};

window.saveAddress = function(e) {
    e.preventDefault();
    const address = document.getElementById('inputBillingAddress')?.value;

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('/settings/address', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.dashboardConfig?.csrfToken || window.settingsConfig?.csrfToken
        },
        body: JSON.stringify({
            alamat: address
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            const displayBillingAddress = document.getElementById('displayBillingAddress');
            if (displayBillingAddress && address) displayBillingAddress.textContent = address;

            window.closeEditAddress();

            Swal.fire({
                title: 'Berhasil!',
                text: 'Alamat tagihan berhasil diperbarui.',
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Gagal!',
                text: data.message || 'Gagal memperbarui alamat.',
                icon: 'error',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
        }
    });
};

window.showTabAlert = function(tabName) {
    Swal.fire({
        title: tabName,
        text: `Halaman ${tabName} pelanggan akan segera hadir!`,
        icon: 'info',
        confirmButtonColor: '#00b207',
        customClass: {
            popup: 'rounded-4'
        }
    });
};

// Cancel Order from Dashboard/History
window.cancelOrder = function(id, orderNumber) {
    Swal.fire({
        title: 'ALASAN PEMBATALAN PESANAN',
        html: `
        <div style="text-align: left; margin-top: 10px;">
            <label class="cancel-reason-option selected" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Salah memilih alamat pengiriman" checked>
                <span>Salah memilih alamat pengiriman</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Salah memilih produk atau varian kue">
                <span>Salah memilih produk atau varian kue</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Ingin mengganti metode pembayaran">
                <span>Ingin mengganti metode pembayaran</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Ingin mengubah atau menambah pesanan">
                <span>Ingin mengubah atau menambah pesanan</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Lainnya">
                <span>Lainnya (Alasan pribadi)</span>
            </label>
        </div>
    `,
        showCancelButton: true,
        confirmButtonText: 'Konfirmasi Batal',
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#00b207',
        cancelButtonColor: '#ffffff',
        customClass: {
            popup: 'cancel-reason-popup',
            title: 'cancel-reason-title',
            cancelButton: 'cancel-reason-cancel-btn',
            confirmButton: 'cancel-reason-confirm-btn'
        },
        didOpen: () => {
            if (!document.getElementById('cancelReasonStyles')) {
                const style = document.createElement('style');
                style.id = 'cancelReasonStyles';
                style.textContent = `
                .cancel-reason-popup { border-radius: 12px !important; padding: 30px 20px !important; max-width: 520px !important; }
                .cancel-reason-title { font-size: 16px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; text-align: left !important; color: #1a1a1a !important; padding: 0 10px !important; }
                .cancel-reason-option { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border: 1.5px solid #e6e6e6; border-radius: 8px; cursor: pointer; transition: all 0.25s ease; background: white; margin-bottom: 10px; font-size: 14px; color: #333; }
                .cancel-reason-option:hover { border-color: #00b207; background-color: #f8fff8; }
                .cancel-reason-option.selected { border-color: #00b207; background-color: #f0fdf0; }
                .cancel-reason-option input[type="radio"] { appearance: none; -webkit-appearance: none; width: 20px; height: 20px; border: 2px solid #d0d0d0; border-radius: 50%; position: relative; cursor: pointer; flex-shrink: 0; margin: 0; transition: all 0.25s ease; }
                .cancel-reason-option input[type="radio"]:checked { border-color: #00b207; }
                .cancel-reason-option input[type="radio"]:checked::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background-color: #00b207; border-radius: 50%; }
                .cancel-reason-option span { flex: 1; }
                .cancel-reason-cancel-btn { border: 2px solid #00b207 !important; color: #00b207 !important; background: white !important; border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
                .cancel-reason-cancel-btn:hover { background: #f0fdf0 !important; }
                .cancel-reason-confirm-btn { border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
            `;
                document.head.appendChild(style);
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const selectedRadio = document.querySelector('input[name="cancel_reason"]:checked');
            const cancelReason = selectedRadio ? selectedRadio.value : 'Lainnya';

            Swal.fire({
                title: 'Memproses...',
                text: 'Harap tunggu.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/order-history/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ cancel_reason: cancelReason })
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: data.message || 'Pesanan Anda telah berhasil dibatalkan.',
                        icon: 'success',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Gagal membatalkan pesanan.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Koneksi ke server gagal saat membatalkan pesanan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
                console.error(err);
            });
        }
    });
};

window.selectCancelReason = function(el) {
    document.querySelectorAll('.cancel-reason-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
};

window.trackOrder = function(orderParam, orderId) {
    if (typeof orderParam === 'object' && orderParam !== null) {
        if (typeof openOrderHistoryTracking === 'function') {
            openOrderHistoryTracking(orderParam);
            return;
        }
    }
    const btn = document.querySelector(`[data-tracking-order="${orderParam}"]`);
    if (btn) {
        btn.click();
        return;
    }
    if (orderId) {
        window.location.href = `/order-history/${orderId}?track=1`;
        return;
    }
    window.location.href = `/order-history`;
};

// Avatar Upload handling in settings
window.handleAvatarFile = function(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', window.settingsConfig?.csrfToken || window.dashboardConfig?.csrfToken);

        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const route = window.settingsConfig?.settingsAvatarRoute || '/settings/avatar';

        fetch(route, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                const img = document.getElementById('avatarImage');
                if (img) img.src = data.avatar_url;
                const dashboardAvatar = document.getElementById('dashboardAvatarImage');
                if (dashboardAvatar) dashboardAvatar.src = data.avatar_url;

                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Foto profil berhasil diperbarui.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal memperbarui foto profil.',
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
};

// Save Settings API Calls
window.saveAccountSettings = function(e) {
    e.preventDefault();
    if (!window.settingsConfig) return;

    const fullNameEl = document.getElementById('inputFullName');
    let fullName = '';
    if (fullNameEl) {
        fullName = fullNameEl.value.trim();
    } else {
        const firstName = document.getElementById('inputFirstName')?.value || '';
        const lastName = document.getElementById('inputLastName')?.value || '';
        fullName = (firstName + ' ' + lastName).trim();
    }
    const email = document.getElementById('inputAccountEmail')?.value || '';
    const phone = document.getElementById('inputAccountPhone')?.value || '';

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(window.settingsConfig.settingsAccountRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.settingsConfig.csrfToken
            },
            body: JSON.stringify({
                nama: fullName,
                email: email,
                no_hp: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                try {
                    localStorage.removeItem('zyh_cust_name');
                    localStorage.removeItem('zyh_cust_address');
                    localStorage.removeItem('zyh_cust_email');
                    localStorage.removeItem('zyh_cust_phone');
                } catch (e) {}
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Pengaturan akun berhasil disimpan.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                }).then(() => {
                    window.location.reload();
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
};

window.saveBillingAddress = function(e) {
    e.preventDefault();

    const street = document.getElementById('inputBillingStreet')?.value || '';
    const notes = document.getElementById('inputBillingNotes')?.value || '';

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const route = window.settingsConfig?.settingsAddressRoute || '/settings/address';

    fetch(route, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.settingsConfig.csrfToken
        },
        body: JSON.stringify({
            alamat: street,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Alamat tagihan berhasil disimpan.',
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Gagal!',
                text: data.message || 'Gagal menyimpan alamat.',
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
};

window.saveNewPassword = function(e) {
    e.preventDefault();
    if (!window.settingsConfig) return;

    const currentPassword = document.getElementById('inputCurrentPassword')?.value;
    const newPassword = document.getElementById('inputNewPassword')?.value;
    const confirmPassword = document.getElementById('inputConfirmPassword')?.value;

    if (newPassword !== confirmPassword) {
        Swal.fire({
            title: 'Password Tidak Cocok!',
            text: 'Konfirmasi password baru tidak cocok.',
            icon: 'warning',
            confirmButtonColor: '#00b207',
            customClass: {
                popup: 'rounded-4'
            }
        });
        return;
    }

    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(window.settingsConfig.settingsPasswordRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.settingsConfig.csrfToken
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                if (document.getElementById('inputCurrentPassword')) document.getElementById('inputCurrentPassword').value = '';
                if (document.getElementById('inputNewPassword')) document.getElementById('inputNewPassword').value = '';
                if (document.getElementById('inputConfirmPassword')) document.getElementById('inputConfirmPassword').value = '';

                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Password berhasil diperbarui.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
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
};

window.togglePasswordVisibility = function(inputId, iconEl) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = iconEl.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
};

// Settings Segmented Tabs Handler
window.switchSettingsTab = function(tabName) {
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });

    document.querySelectorAll('.settings-tab-content').forEach(content => {
        const isTarget = content.id === 'tabContent' + tabName.charAt(0).toUpperCase() + tabName.slice(1);
        content.style.display = isTarget ? 'block' : 'none';
        content.classList.toggle('active', isTarget);
    });

    try {
        history.replaceState(null, '', '#' + tabName);
    } catch (e) {}
};

// Auto switch tab if hash present on load
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash && ['profile', 'address', 'password'].includes(hash)) {
        window.switchSettingsTab(hash);
    }
});


// ==========================================================================
// RETURN ORDER PAGE SPECIFIC LOGIC
// ==========================================================================

window.selectReason = function(el, value) {
    document.querySelectorAll('.reason-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
};

window.selectMethod = function(el) {
    document.querySelectorAll('.method-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
};

// Return Photo upload helper initialization
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
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
                const input = document.getElementById('returnPhotoInput');
                if (input) {
                    input.files = files;
                    window.handlePhotoUpload(input);
                }
            }
        });
    }

    // Load return draft
    if (window.returnOrderConfig) {
        const draft = localStorage.getItem('zyh_return_draft_' + window.returnOrderConfig.orderId);
        if (draft) {
            try {
                const data = JSON.parse(draft);
                const desc = document.getElementById('returnDescription');
                if (desc && data.description) {
                    desc.value = data.description;
                }
                if (data.reason) {
                    document.querySelectorAll('.reason-option').forEach(opt => {
                        const radio = opt.querySelector('input[type="radio"]');
                        if (radio && radio.value === data.reason) {
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
                        if (radio && radio.value === data.method) {
                            opt.classList.add('selected');
                            radio.checked = true;
                        } else {
                            opt.classList.remove('selected');
                        }
                    });
                }
            } catch (e) {}
        }
    }

    // Load order detail address sync
    if (window.orderDetailConfig) {
        const conf = window.orderDetailConfig;
        const storedName = conf.userNama;
        const storedAddress = conf.userAlamat || "";
        const storedEmail = conf.userEmail;
        const storedPhone = conf.userPhone ? "(+62) " + conf.userPhone : "";

        if (storedName) document.querySelectorAll('#displayBillingName').forEach(el => el.textContent = storedName);
        if (storedAddress) document.querySelectorAll('#displayBillingAddress').forEach(el => el.textContent = storedAddress);
        if (storedEmail) document.querySelectorAll('#displayBillingEmail').forEach(el => el.textContent = storedEmail);
        if (storedPhone) document.querySelectorAll('#displayBillingPhone').forEach(el => el.textContent = storedPhone);
    }
});

window.selectedReturnFile = null;

window.triggerPhotoSelect = function() {
    const photoInput = document.getElementById('returnPhotoInput');
    if (photoInput) photoInput.click();
};

window.handlePhotoUpload = function(input) {
    if (input && input.files && input.files[0]) {
        const file = input.files[0];
        window.selectedReturnFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('uploadPreview');
            const icon = document.getElementById('uploadIcon');
            const title = document.getElementById('uploadTitle');
            const hint = document.getElementById('uploadHint');
            const area = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfoArea');
            const fileNameBadge = document.getElementById('fileNameBadge');

            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (icon) icon.style.display = 'none';
            if (title) title.style.display = 'none';
            if (hint) hint.style.display = 'none';
            if (area) area.classList.add('has-preview');

            if (fileInfo && fileNameBadge) {
                fileNameBadge.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                fileInfo.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
};

window.removeSelectedPhoto = function(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    window.selectedReturnFile = null;
    const photoInput = document.getElementById('returnPhotoInput');
    if (photoInput) photoInput.value = '';

    const preview = document.getElementById('uploadPreview');
    const icon = document.getElementById('uploadIcon');
    const title = document.getElementById('uploadTitle');
    const hint = document.getElementById('uploadHint');
    const area = document.getElementById('uploadArea');
    const fileInfo = document.getElementById('fileInfoArea');

    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
    if (icon) icon.style.display = 'block';
    if (title) title.style.display = 'block';
    if (hint) hint.style.display = 'block';
    if (area) {
        area.classList.remove('has-preview');
        area.classList.remove('dragover');
    }
    if (fileInfo) fileInfo.style.display = 'none';
};

// Drag and Drop Event Listeners for Return Photo Upload Area
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                const photoInput = document.getElementById('returnPhotoInput');
                if (photoInput) {
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(files[0]);
                        photoInput.files = dataTransfer.files;
                    } catch (err) {
                        console.warn('DataTransfer fallback:', err);
                    }
                    window.handlePhotoUpload(photoInput);
                }
            }
        }, false);
    }
});

window.saveDraft = function() {
    if (!window.returnOrderConfig) return;

    const reason = document.querySelector('input[name="reason"]:checked')?.value || '';
    const description = document.getElementById('returnDescription')?.value || '';
    const method = document.querySelector('input[name="return_method"]:checked')?.value || '';

    const draftData = {
        order_id: window.returnOrderConfig.orderId,
        order_number: window.returnOrderConfig.orderNumber,
        reason: reason,
        description: description,
        method: method,
        saved_at: new Date().toISOString()
    };

    localStorage.setItem('zyh_return_draft_' + window.returnOrderConfig.orderId, JSON.stringify(draftData));

    Swal.fire({
        title: 'Tersimpan!',
        text: 'Draft pengembalian berhasil disimpan.',
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: {
            popup: 'rounded-4'
        }
    });
};

window.submitReturnOrder = function(e) {
    e.preventDefault();
    if (!window.returnOrderConfig) return;

    const reason = document.querySelector('input[name="reason"]:checked')?.value || '';
    const description = document.getElementById('returnDescription')?.value || '';
    const method = document.querySelector('input[name="return_method"]:checked')?.value || '';

    if (!reason) {
        Swal.fire({
            title: 'Perhatian!',
            text: 'Silakan pilih alasan pengembalian.',
            icon: 'warning',
            confirmButtonColor: '#00b207',
            customClass: {
                popup: 'rounded-4'
            }
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
        customClass: {
            popup: 'rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('reason', reason);
            formData.append('description', description);
            formData.append('return_method', method);

            const photoInput = document.getElementById('returnPhotoInput');
            const fileToSend = window.selectedReturnFile || (photoInput && photoInput.files && photoInput.files.length > 0 ? photoInput.files[0] : null);

            if (fileToSend) {
                formData.append('photo', fileToSend, fileToSend.name || 'retur.jpg');
                formData.append('foto_retur', fileToSend, fileToSend.name || 'retur.jpg');
            }

            fetch(window.returnOrderConfig.returnSubmitRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.returnOrderConfig.csrfToken
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        localStorage.removeItem('zyh_return_draft_' + window.returnOrderConfig.orderId);

                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Pengembalian berhasil diajukan.',
                            icon: 'success',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        }).then(() => {
                            window.location.href = window.returnOrderConfig.orderDetailRoute;
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
};


// ==========================================================================
// ORDER DETAIL PAGE SPECIFIC LOGIC
// ==========================================================================

window.cancelOrderDetail = function(id, orderNumber) {
    Swal.fire({
        title: 'REASON FOR CANCEL ORDERS',
        html: `
        <div style="text-align: left; margin-top: 10px;">
            <label class="cancel-reason-option selected" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Wrong choice of delivery address" checked>
                <span>Wrong choice of delivery address</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Wrong choice of product">
                <span>Wrong choice of product</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Want to change payment method">
                <span>Want to change payment method</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Want to replace or add a product">
                <span>Want to replace or add a product</span>
            </label>
            <label class="cancel-reason-option" onclick="window.selectCancelReason(this)">
                <input type="radio" name="cancel_reason" value="Others">
                <span>Others</span>
            </label>
        </div>
    `,
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#00b207',
        cancelButtonColor: '#ffffff',
        customClass: {
            popup: 'cancel-reason-popup',
            title: 'cancel-reason-title',
            cancelButton: 'cancel-reason-cancel-btn',
            confirmButton: 'cancel-reason-confirm-btn'
        },
        didOpen: () => {
            if (!document.getElementById('cancelReasonStyles')) {
                const style = document.createElement('style');
                style.id = 'cancelReasonStyles';
                style.textContent = `
                .cancel-reason-popup { border-radius: 12px !important; padding: 30px 20px !important; max-width: 520px !important; }
                .cancel-reason-title { font-size: 16px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; text-align: left !important; color: #1a1a1a !important; padding: 0 10px !important; }
                .cancel-reason-option { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border: 1.5px solid #e6e6e6; border-radius: 8px; cursor: pointer; transition: all 0.25s ease; background: white; margin-bottom: 10px; font-size: 14px; color: #333; }
                .cancel-reason-option:hover { border-color: #00b207; background-color: #f8fff8; }
                .cancel-reason-option.selected { border-color: #00b207; background-color: #f0fdf0; }
                .cancel-reason-option input[type="radio"] { appearance: none; -webkit-appearance: none; width: 20px; height: 20px; border: 2px solid #d0d0d0; border-radius: 50%; position: relative; cursor: pointer; flex-shrink: 0; margin: 0; transition: all 0.25s ease; }
                .cancel-reason-option input[type="radio"]:checked { border-color: #00b207; }
                .cancel-reason-option input[type="radio"]:checked::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background-color: #00b207; border-radius: 50%; }
                .cancel-reason-option span { flex: 1; }
                .cancel-reason-cancel-btn { border: 2px solid #00b207 !important; color: #00b207 !important; background: white !important; border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
                .cancel-reason-cancel-btn:hover { background: #f0fdf0 !important; }
                .cancel-reason-confirm-btn { border-radius: 50px !important; font-weight: 600 !important; padding: 10px 30px !important; font-size: 14px !important; }
            `;
                document.head.appendChild(style);
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/order-history/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.orderDetailConfig?.csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            title: 'Dibatalkan!',
                            text: data.message || 'Pesanan Anda telah berhasil dibatalkan.',
                            icon: 'success',
                            confirmButtonColor: '#00b207',
                            customClass: { popup: 'rounded-4' }
                        }).then(() => {
                            window.location.href = window.orderDetailConfig?.orderHistoryRoute || "";
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Gagal membatalkan pesanan.',
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
};


// ==========================================================================
// DETAIL PAGE SPECIFIC LOGIC
// ==========================================================================

window.switchImage = function(src, thumbEl) {
    const mainImg = document.getElementById('mainProductImage');
    if (mainImg) {
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 150);
    }
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    if (thumbEl) thumbEl.classList.add('active');
};

window.changeQty = function(delta) {
    const input = document.getElementById('qtyInput');
    if (input) {
        let currentVal = parseInt(input.value);
        if (isNaN(currentVal) || currentVal < 1) currentVal = 1;
        let val = currentVal + delta;
        if (val < 1) val = 1;

        let maxStock = parseInt(input.getAttribute('max'));
        if (isNaN(maxStock) && window.detailConfig?.productStock !== undefined) {
            maxStock = parseInt(window.detailConfig.productStock);
        }

        const warningEl = document.getElementById('qtyStockWarning');
        if (!isNaN(maxStock) && maxStock > 0) {
            if (val >= maxStock) {
                val = maxStock;
                if (warningEl) {
                    warningEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa ${maxStock} unit)`;
                    warningEl.style.display = 'block';
                }
            } else {
                if (warningEl) warningEl.style.display = 'none';
            }
        } else {
            if (warningEl) warningEl.style.display = 'none';
        }
        input.value = val;
    }
};
window.switchTab = function(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    const tab = document.getElementById('tab-' + tabId);
    if (tab) tab.classList.add('active');
    if (btn) btn.classList.add('active');
};

window.buyNow = function() {
    if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
        window.showAdminShoppingNotice();
        return false;
    }
    if (!window.isLoggedIn) {
        window.location.href = window.detailConfig?.loginRoute || "/login";
        return;
    }

    if (window.detailConfig) {
        const conf = window.detailConfig;
        window.addToCart({
            id: conf.productId,
            name: conf.productName,
            price: conf.productPrice,
            bisa_custom_teks: conf.bisaCustomTeks || false,
            img: conf.productImg,
            qty: parseInt(document.getElementById('qtyInput')?.value || 1)
        }, false);
        window.location.href = conf.cartRoute || window.layoutConfig?.cartRoute || "/cart";
    }
};

window.buyCustomNow = function() {
    if (window.detailConfig) {
        const conf = window.detailConfig;
        const qty = parseInt(document.getElementById('qtyInput')?.value || 1);
        const formattedPrice = new Intl.NumberFormat('id-ID').format(conf.productPrice);
        const sName = window.storeName || "Zyh Corner";
        const sPhone = window.storePhone || "628123456789";
        const message = encodeURIComponent("Halo " + sName + ", saya tertarik untuk memesan Custom Product: " + conf.productName + " (Harga: Rp. " + formattedPrice + ") sebanyak " + qty + " pcs. Mohon info lebih lanjut.");
        window.open("https://wa.me/" + sPhone + "?text=" + message, "_blank");
    }
};


// ==========================================================================
// DYNAMIC DOM RENDERING HELPERS FOR CART PAGE & CHECKOUT
// ==========================================================================

function renderCartPage(container, cartItems, subtotal) {
    if (cartItems.length === 0) {
        container.innerHTML = `
            <div class="empty-cart-page" style="text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-bag-shopping" style="font-size: 60px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 10px;">Keranjang belanja Anda kosong</h3>
                <p style="color: #666; margin-bottom: 30px;">Tambahkan beberapa produk lezat untuk memulai belanja Anda.</p>
                <a href="/shop" class="btn-tracking-solid" style="padding: 12px 30px; display: inline-block;">Pergi ke Toko</a>
            </div>
        `;
        return;
    }

    // Dynamic cart UI wrapper
    container.innerHTML = `
        <div class="cart-layout-grid">
            <!-- Left Column: Products Card & Table -->
            <div class="cart-table-wrapper">
                <div style="overflow-x: auto;">
                    <table class="cart-table" style="width: 100%; border-collapse: collapse; min-width: 580px;">
                        <thead>
                            <tr style="background: #fafafa; border-bottom: 1.5px solid #e6e6e6; text-align: left;">
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Product</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Price</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Quantity</th>
                                <th style="padding: 16px 20px; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Subtotal</th>
                                <th style="padding: 16px 20px; width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            ${cartItems.map(item => `
                                <tr style="border-bottom: 1px solid #f2f2f2;">
                                    <td style="padding: 18px 20px;">
                                        <div class="cart-product-cell" style="display: flex; align-items: center; gap: 14px;">
                                            <div class="cart-product-img" style="width: 72px; height: 72px; border-radius: 8px; overflow: hidden; background: #fdfdfd; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; flex-shrink: 0;">
                                                <img src="${item.img}" alt="${item.name}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            </div>
                                            <div style="display: flex; flex-direction: column;">
                                                <span class="cart-product-name" style="font-size: 14.5px; font-weight: 600; color: #1a1a1a;">${item.name}</span>
                                                ${item.custom_text ? `<small style="color: #00b207; font-size: 12px; margin-top: 3px;">Custom Teks: "${item.custom_text}"</small>` : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 18px 20px; font-size: 14px; font-weight: 500; color: #4d4d4d; white-space: nowrap;">
                                        Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}
                                    </td>
                                    <td style="padding: 18px 20px;">
                                        <div class="cart-qty-wrapper" style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <div class="cart-qty-box" style="display: inline-flex; align-items: center; border: 1.5px solid #e6e6e6; border-radius: 50px; background: #fff; width: 104px; height: 36px; padding: 2px; justify-content: space-between;">
                                                <button type="button" class="cart-qty-btn" onclick="window.updateQty('${item.id}', -1)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #f5f5f5; color: #333; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">-</button>
                                                <input type="text" class="cart-qty-input" value="${item.qty}" readonly style="width: 36px; border: none; text-align: center; font-weight: 600; font-size: 13.5px; background: transparent; outline: none;">
                                                <button type="button" class="cart-qty-btn" onclick="window.updateQty('${item.id}', 1)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #f5f5f5; color: #333; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">+</button>
                                            </div>
                                            <span id="cartQtyWarning_${item.id}" class="cart-stock-warning" style="display: none; color: #dc2626; font-size: 11px; font-weight: 500; margin-top: 4px; line-height: 1.2;">
                                                <i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maks. stok
                                            </span>
                                        </div>
                                    </td>
                                    <td style="padding: 18px 20px; text-align: right;">
                                        <button type="button" class="cart-remove-btn" onclick="window.removeFromCart('${item.id}')" title="Hapus produk" style="background: none; border: 1px solid #e6e6e6; width: 28px; height: 28px; border-radius: 50%; color: #999; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                            <i class="fa-solid fa-xmark" style="font-size: 14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="cart-table-actions" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #fff; border-top: 1px solid #f2f2f2;">
                    <a href="/shop" class="btn-action-gray" style="background: #f5f5f5; color: #333; border: 1px solid #e6e6e6; border-radius: 50px; padding: 10px 22px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;">
                        <i class="fa-solid fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>

            <!-- Right Column: Card Cart Totals -->
            <div class="cart-total-card">
                <h3 class="cart-total-title" style="font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #f2f2f2;">Cart Totals</h3>
                <div class="summary-calc-list">
                    <div class="cart-total-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px; color: #64748b;">
                        <span>Subtotal</span>
                        <span style="font-weight: 600; color: #1a1a1a;">Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                    <div class="cart-total-row" style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px; color: #64748b;">
                        <span>Total Jenis</span>
                        <span style="font-weight: 600; color: #1a1a1a;">${cartItems.length} Jenis Produk</span>
                    </div>
                    <div class="cart-total-row grand-total" style="display: flex; justify-content: space-between; padding: 16px 0 24px; font-size: 16px; color: #1a1a1a;">
                        <span style="font-weight: 700;">Total Belanja</span>
                        <span style="font-size: 18px; font-weight: 700; color: #024220;">Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                </div>
                <a href="${window.layoutConfig?.checkoutRoute || '/checkout'}" class="btn-checkout-green">
                    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    `;
}

function renderOrderSummary(container, cartItems, subtotal) {
    if (cartItems.length === 0) {
        container.innerHTML = `<p style="color: #999; padding: 10px 0;">No items in cart</p>`;
        return;
    }

    container.innerHTML = cartItems.map(item => `
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f2f2f2;">
            <div style="display: flex; align-items: center; gap: 10px; width: 70%;">
                <div style="width: 45px; height: 45px; border-radius: 4px; overflow: hidden; border: 1px solid #e6e6e6; background: #f9f9f9; display: flex; align-items: center; justify-content: center; padding: 2px; flex-shrink: 0;">
                    <img src="${item.img}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <span style="font-size: 13.5px; font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.name} x ${item.qty}</span>
            </div>
            <span style="font-size: 13.5px; font-weight: 600; color: #1a1a1a;">Rp. ${new Intl.NumberFormat('id-ID').format(item.price * item.qty)}</span>
        </div>
    `).join('');

    const subtotalEl = document.getElementById('summarySubtotal');
    const totalEl = document.getElementById('summaryTotal');
    if (subtotalEl) subtotalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
    if (totalEl) totalEl.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotal);
}

// ==========================================================================
// SHOP PAGE SPECIFIC FILTER LOGIC
// ==========================================================================

document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.filter-toolbar .filter-select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            window.applyShopFilters();
        });
    });
});

window.applyShopFilters = function() {
    const category = document.getElementById('filterCategory')?.value;
    const price = document.getElementById('filterPrice')?.value;
    const sortBy = document.getElementById('filterSortBy')?.value;
    const show = document.getElementById('filterShow')?.value;

    const url = new URL(window.location.href);
    if (category) url.searchParams.set('category', category);
    else url.searchParams.delete('category');

    if (price) url.searchParams.set('price', price);
    else url.searchParams.delete('price');

    if (sortBy) url.searchParams.set('sort_by', sortBy);
    else url.searchParams.delete('sort_by');

    if (show) url.searchParams.set('show', show);
    else url.searchParams.delete('show');

    url.searchParams.delete('page'); // Reset page when filters change
    window.location.href = url.toString();
};

window.removeShopFilter = function(paramName) {
    const url = new URL(window.location.href);
    url.searchParams.delete(paramName);
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

// ==========================================================================
// CHECKOUT PAGE SPECIFIC LOGIC (RESTORED)
// ==========================================================================
let checkoutMap, checkoutMarker;

window.initCheckoutMap = function() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;

    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const savedLat = (latEl && latEl.value) ? parseFloat(latEl.value) : null;
    const savedLng = (lngEl && lngEl.value) ? parseFloat(lngEl.value) : null;

    // Saved coordinates or Zyh Corner in Pangkalan Kerinci, Pelalawan as starting fallback point
    const initialCoords = (savedLat && savedLng && !isNaN(savedLat) && !isNaN(savedLng)) 
        ? [savedLat, savedLng] 
        : [0.40384, 101.85588];
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.warn('Leaflet map library is not loaded.');
        return;
    }
    
    checkoutMap = L.map('map', {
        scrollWheelZoom: false
    }).setView(initialCoords, 15);
    window.checkoutMap = checkoutMap;
    // Add premium styling layer from OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(checkoutMap);

    // Create draggable marker
    checkoutMarker = L.marker(initialCoords, {
        draggable: true
    }).addTo(checkoutMap);
    window.checkoutMarker = checkoutMarker;
    // Set initial coordinates values
    window.updateCoordsInputs(initialCoords[0], initialCoords[1]);

    // Dragend event on marker
    checkoutMarker.on('dragend', function (e) {
        let latlng = checkoutMarker.getLatLng();
        window.updateCoordsInputs(latlng.lat, latlng.lng);
    });

    // Click event on map
    checkoutMap.on('click', function (e) {
        let latlng = e.latlng;
        checkoutMarker.setLatLng(latlng);
        window.updateCoordsInputs(latlng.lat, latlng.lng);
    });
};

window.updateCoordsInputs = function(lat, lng) {
    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const displayEl = document.getElementById('coordsDisplay');
    const sumGps = document.getElementById('summaryGpsBadge');
    if (latEl) latEl.value = lat;
    if (lngEl) lngEl.value = lng;
    if (displayEl) {
        displayEl.innerHTML = `Titik koordinat terpilih: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    }
    if (sumGps) {
        sumGps.style.display = 'inline-flex';
    }
};

// ==========================================================================
// CHECKOUT MAP LOCATION SEARCH & GEOLOCATION
// ==========================================================================
window.toggleClearCheckoutSearchBtn = function() {
    const inputEl = document.getElementById('checkoutMapSearchInput');
    const clearBtn = document.getElementById('checkoutClearSearchBtn');
    if (clearBtn) {
        clearBtn.style.display = (inputEl && inputEl.value.trim().length > 0) ? 'block' : 'none';
    }
};

window.clearCheckoutMapSearch = function() {
    const inputEl = document.getElementById('checkoutMapSearchInput');
    const clearBtn = document.getElementById('checkoutClearSearchBtn');
    const resultsContainer = document.getElementById('checkoutMapSearchResults');
    if (inputEl) {
        inputEl.value = '';
    }
    if (clearBtn) clearBtn.style.display = 'none';
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
    }
};

window.searchLocationOnCheckoutMap = function() {
    const inputEl = document.getElementById('checkoutMapSearchInput');
    const resultsContainer = document.getElementById('checkoutMapSearchResults');
    const query = inputEl ? inputEl.value.trim() : '';

    if (!query) {
        if (inputEl) inputEl.focus();
        return;
    }

    if (!resultsContainer) return;

    resultsContainer.innerHTML = `
        <div class="map-search-loading">
            <i class="fa-solid fa-spinner fa-spin" style="color: #00b207;"></i>
            <span>Mencari titik lokasi di peta...</span>
        </div>
    `;
    resultsContainer.style.display = 'block';

    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=5`;

    fetch(url, {
        headers: { 'Accept-Language': 'id' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data || data.length === 0) {
            resultsContainer.innerHTML = `
                <div class="map-search-empty">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    Lokasi tidak ditemukan. Coba gunakan nama jalan atau daerah yang lebih umum.
                </div>
            `;
            return;
        }

        let html = '';
        data.forEach(item => {
            const rawTitle = item.name || query;
            const cleanTitle = rawTitle.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const cleanDisplay = (item.display_name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const lat = parseFloat(item.lat);
            const lon = parseFloat(item.lon);

            html += `
                <div class="map-search-item" onclick="selectCheckoutMapSearchResult(${lat}, ${lon}, '${cleanTitle}')">
                    <div class="map-search-item-title">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>${cleanTitle}</span>
                    </div>
                    <div class="map-search-item-sub">${cleanDisplay}</div>
                </div>
            `;
        });
        resultsContainer.innerHTML = html;
    })
    .catch(err => {
        console.error('OSM Geocoding search error:', err);
        resultsContainer.innerHTML = `
            <div class="map-search-empty">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Gagal memuat hasil pencarian. Periksa koneksi internet Anda.
            </div>
        `;
    });
};

window.selectCheckoutMapSearchResult = function(lat, lon, title) {
    if (checkoutMap && checkoutMarker) {
        checkoutMap.flyTo([lat, lon], 17);
        checkoutMarker.setLatLng([lat, lon]);
        window.updateCoordsInputs(lat, lon);
    }

    const inputEl = document.getElementById('checkoutMapSearchInput');
    const resultsContainer = document.getElementById('checkoutMapSearchResults');

    if (inputEl && title) {
        inputEl.value = title;
        window.toggleClearCheckoutSearchBtn();
    }
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
};

window.locateUserPositionCheckout = function() {
    if (!navigator.geolocation) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Geolocation Tidak Didukung',
                text: 'Browser Anda tidak mendukung deteksi lokasi otomatis.',
                icon: 'warning'
            });
        } else {
            alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
        }
        return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Mendeteksi Lokasi...',
            text: 'Mohon tunggu, sedang mencari koordinat GPS Anda...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            if (typeof Swal !== 'undefined') Swal.close();
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            if (checkoutMap && checkoutMarker) {
                checkoutMap.flyTo([lat, lng], 17);
                checkoutMarker.setLatLng([lat, lng]);
                window.updateCoordsInputs(lat, lng);
            }
        },
        function(error) {
            if (typeof Swal !== 'undefined') {
                Swal.close();
                Swal.fire({
                    title: 'Lokasi Tidak Ditemukan',
                    text: 'Harap izinkan akses lokasi pada browser Anda.',
                    icon: 'warning'
                });
            } else {
                alert('Harap izinkan akses lokasi pada browser Anda.');
            }
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
};

document.addEventListener('click', function(e) {
    const checkoutContainer = document.getElementById('checkoutMapSearchContainer');
    if (checkoutContainer && !checkoutContainer.contains(e.target)) {
        const checkoutResults = document.getElementById('checkoutMapSearchResults');
        if (checkoutResults) checkoutResults.style.display = 'none';
    }
});

window.renderOrderSummaryCheckout = function() {
    const orderSummaryDiv = document.getElementById('orderItemsSummary');
    if (!orderSummaryDiv) return;

    let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
    
    if (localCart.length === 0) {
        orderSummaryDiv.innerHTML = '<p style="color:#94a3b8; font-style:italic; text-align:center; padding:20px 0;">Keranjang belanja kosong</p>';
        window.location.href = window.checkoutConfig?.userChartRoute || "/cart";
        return;
    }

    const itemCountEl = document.getElementById('summaryItemCount');
    if (itemCountEl) {
        const totalQty = localCart.reduce((sum, item) => sum + (parseInt(item.qty) || 1), 0);
        itemCountEl.textContent = `${totalQty} Produk`;
    }

    let summaryRowsHtml = localCart.map(item => {
        let priceSub = item.price * item.qty;
        let priceSubFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(priceSub);
        let customTextHtml = item.custom_text ? `<div class="order-item-custom-text" title="Teks ucapan kue"><i class="fa-solid fa-pen-nib" style="margin-right: 4px;"></i>"${item.custom_text}"</div>` : '';
        
        return `
            <div class="order-item-row">
                <div class="order-item-left">
                    <div class="order-item-img">
                        <img src="${item.img}" alt="${item.name}">
                    </div>
                    <div class="order-item-info">
                        <span class="order-item-name" title="${item.name}">${item.name}</span>
                        <span class="order-item-qty">Qty: <strong>${item.qty} pcs</strong></span>
                        ${customTextHtml}
                    </div>
                </div>
                <span class="order-item-price">${priceSubFormatted}</span>
            </div>
        `;
    }).join('');

    orderSummaryDiv.innerHTML = summaryRowsHtml;

    // Subtotals
    let subtotalVal = localCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    let subtotalValFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(subtotalVal);
    
    const subtotalEl = document.getElementById('summarySubtotal');
    const totalEl = document.getElementById('summaryTotal');
    if (subtotalEl) subtotalEl.innerHTML = subtotalValFormatted;
    if (totalEl) totalEl.innerHTML = subtotalValFormatted;
};

window.selectShippingOption = function(cardEl) {
    if (!cardEl) return;
    document.querySelectorAll('.shipping-card-option').forEach(el => el.classList.remove('active'));
    cardEl.classList.add('active');
    const radio = cardEl.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;

    const costAttr = cardEl.getAttribute('data-cost');
    const cost = costAttr !== null ? parseInt(costAttr, 10) : 0;
    window.currentShippingCost = isNaN(cost) ? 0 : cost;
    if (typeof window.updateOrderTotalsWithShipping === 'function') {
        window.updateOrderTotalsWithShipping();
    }
};

window.selectPaymentMethod = function(method) {
    // Toggle active style
    document.querySelectorAll('.payment-option-item').forEach(item => item.classList.remove('active'));
    
    const codRadio = document.getElementById('payCod');
    const transferRadio = document.getElementById('payTransfer');
    const ewalletRadio = document.getElementById('payEwallet');

    const transferSub = document.getElementById('transferSubOptions');
    const ewalletSub = document.getElementById('ewalletSubOptions');

    if (transferSub) transferSub.style.display = 'none';
    if (ewalletSub) ewalletSub.style.display = 'none';

    if (method === 'cod' && codRadio) {
        codRadio.checked = true;
        const parent = codRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
    } else if (method === 'transfer' && transferRadio) {
        transferRadio.checked = true;
        const parent = transferRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
        if (transferSub) transferSub.style.display = 'block';
    } else if (method === 'ewallet' && ewalletRadio) {
        ewalletRadio.checked = true;
        const parent = ewalletRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
        if (ewalletSub) ewalletSub.style.display = 'block';
    }
};

if (!window.submitOrder) {
    window.submitOrder = function() {
        const form = document.getElementById('checkoutForm');
        if (form && !form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
        if (localCart.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Keranjang belanja kosong!',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        // Gather details
        const addrEl = document.getElementById('streetAddress');
        const notesEl = document.getElementById('orderNotes');
        const latEl = document.getElementById('latitude');
        const lngEl = document.getElementById('longitude');
        let streetAddress = addrEl ? addrEl.value : '';
        let notes = notesEl ? notesEl.value : '';
        let latitudeVal = latEl && latEl.value ? parseFloat(latEl.value) : null;
        let longitudeVal = lngEl && lngEl.value ? parseFloat(lngEl.value) : null;
        const payRadio = document.querySelector('input[name="paymentGroup"]:checked');
        const shipRadio = document.querySelector('input[name="shippingGroup"]:checked');
        let paymentVal = payRadio ? payRadio.value : 'COD';
        let shippingVal = shipRadio ? shipRadio.value : 'Maxim';

        // Show premium processing spinner
        Swal.fire({
            title: 'Memproses Pesanan...',
            text: 'Harap tunggu sementara kami memverifikasi pesanan Anda.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const routeUrl = window.checkoutConfig?.checkoutPostRoute || "/checkout";
        const token = window.checkoutConfig?.csrfToken || "";

        // Send AJAX request
        fetch(routeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                items: localCart,
                address: streetAddress,
                notes: notes,
                payment_method: paymentVal,
                shipping_method: shippingVal,
                latitude: latitudeVal,
                longitude: longitudeVal
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Clear the cart on successful database entry
                localStorage.removeItem('zyh_cart');
                if (typeof window.updateHeaderCart === 'function') {
                    window.cart = [];
                    window.updateHeaderCart();
                }
                window.location.href = (window.checkoutConfig?.orderSuccessRoute || "/order-success") + '?order_id=' + data.order.id;
            } else {
                Swal.fire({
                    title: 'Kesalahan!',
                    text: data.message || 'Terjadi kesalahan saat memproses pesanan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal. Harap coba lagi.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
    };
}

// DOMContentLoaded triggers for checkout page
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('orderItemsSummary')) {
        window.renderOrderSummaryCheckout();
    }
    if (document.getElementById('map')) {
        window.initCheckoutMap();
    }

    // Live search autocomplete initialization
    setupLiveSearch('headerSearchInput', 'headerSearchSuggestions', document.getElementById('headerSearchForm'));
    setupLiveSearch('mobileSearchInput', 'mobileSearchSuggestions', document.getElementById('mobileSearchForm'));
});

// ==========================================================================
// LIVE SEARCH AUTOCOMPLETE & DEBOUNCE LOGIC
// ==========================================================================

function setupLiveSearch(inputId, dropdownId, formEl) {
    const inputEl = document.getElementById(inputId);
    const dropdownEl = document.getElementById(dropdownId);
    if (!inputEl || !dropdownEl) return;

    let debounceTimer = null;

    inputEl.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            dropdownEl.style.display = 'none';
            dropdownEl.innerHTML = '';
            return;
        }

        // Show loading state immediately while user stops typing
        dropdownEl.style.display = 'block';
        dropdownEl.innerHTML = `
            <div class="search-suggestion-loading" style="padding: 14px; text-align: center; color: #888; font-size: 13px;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px; color: #00b207;"></i> Mencari produk...
            </div>
        `;

        // Debounce timer: wait 350ms after user stops typing
        debounceTimer = setTimeout(() => {
            fetchLiveSearchSuggestions(query, dropdownEl, formEl);
        }, 350);
    });

    // Close on click outside or Escape
    document.addEventListener('click', function(e) {
        if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
            dropdownEl.style.display = 'none';
        }
    });

    inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdownEl.style.display = 'none';
        }
    });

    inputEl.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && dropdownEl.children.length > 0) {
            dropdownEl.style.display = 'block';
        }
    });
}

function fetchLiveSearchSuggestions(query, dropdownEl, formEl) {
    const route = window.layoutConfig?.searchSuggestionsRoute || '/search-suggestions';
    const targetUrl = `${route}?q=${encodeURIComponent(query)}`;

    fetch(targetUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data) || data.length === 0) {
            dropdownEl.style.display = 'block';
            dropdownEl.innerHTML = `
                <div style="padding: 16px; text-align: center; color: #777; font-size: 13.5px;">
                    <i class="fa-solid fa-face-frown" style="font-size: 20px; color: #bbb; display: block; margin-bottom: 6px;"></i>
                    Tidak ada produk yang cocok dengan "<strong>${escapeHtml(query)}</strong>"
                </div>
            `;
            return;
        }

        let html = '<div class="search-suggestions-list" style="max-height: 360px; overflow-y: auto;">';

        data.forEach(item => {
            const stockBadge = (item.stok === '0' || String(item.stok).toLowerCase() === 'habis')
                ? '<span style="font-size: 11px; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-weight: 500;">Habis</span>'
                : '';

            const customBadge = item.is_kustom
                ? '<span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: 500; margin-left: 4px;">Kustom</span>'
                : '';

            html += `
                <a href="${item.url}" class="search-suggestion-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; text-decoration: none; color: inherit; border-bottom: 1px solid #f2f2f2; transition: background 0.2s ease;">
                    <img src="${item.image}" alt="${escapeHtml(item.name)}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; flex-shrink: 0; background: #f9f9f9;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 14px; color: #1a1a1a; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            ${escapeHtml(item.name)}
                        </div>
                        <div style="font-size: 12px; color: #888; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                            <span>${escapeHtml(item.category)}</span>
                            ${customBadge}
                            ${stockBadge}
                        </div>
                    </div>
                    <div style="font-weight: 600; font-size: 13.5px; color: #00b207; white-space: nowrap; flex-shrink: 0;">
                        ${item.price}
                    </div>
                </a>
            `;
        });

        html += '</div>';

        const formAction = formEl ? formEl.getAttribute('action') : '/shop';
        const searchUrl = `${formAction}?search=${encodeURIComponent(query)}`;

        html += `
            <a href="${searchUrl}" class="search-suggestion-footer" style="display: block; text-align: center; padding: 11px 14px; background: #f8faf8; color: #00b207; font-weight: 600; font-size: 13px; text-decoration: none; border-top: 1px solid #e6e6e6; transition: background 0.2s;">
                Lihat semua hasil untuk "${escapeHtml(query)}" <i class="fa-solid fa-arrow-right" style="margin-left: 4px; font-size: 11px;"></i>
            </a>
        `;

        dropdownEl.style.display = 'block';
        dropdownEl.innerHTML = html;
    })
    .catch(err => {
        console.error('Error fetching search suggestions:', err);
        dropdownEl.style.display = 'none';
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

