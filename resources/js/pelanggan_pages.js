/**
 * Zyh Corner - Customer Pages Common & Page-Specific Scripts
 */

// Initialize logged-in state from configuration
window.isLoggedIn = window.layoutConfig?.isLoggedIn || false;

// --- Product Modal Logic ---
window.openProductModal = function(btn) {
    const data = btn.dataset;

    const pmName = document.getElementById('pmName');
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

    if (pmName) pmName.textContent = data.name;
    if (pmQtyInput) pmQtyInput.value = 1;

    let price = parseInt(data.price);
    if (pmNewPrice) {
        pmNewPrice.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price);
    }

    if (data.sale === '1') {
        if (pmOldPrice) {
            pmOldPrice.style.display = 'inline-block';
            pmOldPrice.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price * 2);
        }
        if (pmDiscountBadge) {
            pmDiscountBadge.style.display = 'inline-block';
            pmDiscountBadge.textContent = '50% Off';
        }
    } else {
        if (pmOldPrice) pmOldPrice.style.display = 'none';
        if (pmDiscountBadge) pmDiscountBadge.style.display = 'none';
    }

    const isCustom = data.custom === '1';
    if (pmCartBtn) {
        pmCartBtn.style.display = isCustom ? 'none' : '';
    }

    if (pmStock) {
        if (data.stock == '0' && !isCustom) {
            pmStock.textContent = 'Habis';
            pmStock.className = 'pm-stock-badge out';

            if (pmBuyBtn) {
                pmBuyBtn.style.backgroundColor = '';
                pmBuyBtn.classList.add('disabled');
                pmBuyBtn.disabled = true;
                pmBuyBtn.innerHTML = 'Stok Habis <i class="fa-solid fa-ban"></i>';
                pmBuyBtn.onclick = null;
            }
            if (pmCartBtn) {
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
            if (data.stock == '0' && isCustom) {
                pmStock.textContent = 'Pre-Order / Hubungi Penjual';
                pmStock.className = 'pm-stock-badge out';
            } else {
                pmStock.textContent = 'Tersedia';
                pmStock.className = 'pm-stock-badge';
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
                        const message = encodeURIComponent("Halo Zyh Corner, saya tertarik untuk memesan Custom Product: " + data.name + " (Harga: Rp. " + formattedPrice + ") sebanyak " + qty + " pcs. Mohon info lebih lanjut.");
                        window.open("https://wa.me/628123456789?text=" + message, "_blank");
                    };
                } else {
                    pmBuyBtn.style.backgroundColor = '';
                    pmBuyBtn.innerHTML = 'Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>';
                    pmBuyBtn.onclick = function() {
                        if (!window.isLoggedIn) {
                            window.closeProductModal();
                            window.location.href = window.layoutConfig?.loginRoute || "/login";
                            return;
                        }
                        window.addToCart({
                            id: data.id,
                            name: data.name,
                            price: price,
                            img: data.f1 || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop',
                            qty: parseInt(document.getElementById('pmQtyInput').value)
                        });
                        window.location.href = window.layoutConfig?.checkoutRoute || "/checkout";
                    };
                }
            }
            if (pmCartBtn) {
                pmCartBtn.classList.remove('disabled');
                pmCartBtn.disabled = false;
                pmCartBtn.onclick = function() {
                    window.addToCart({
                        id: data.id,
                        name: data.name,
                        price: price,
                        img: data.f1 || 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop',
                        qty: parseInt(document.getElementById('pmQtyInput').value)
                    });
                    window.closeProductModal();
                };
            }
            const pmQtyContainer = document.querySelector('.pm-qty');
            if (pmQtyContainer) {
                pmQtyContainer.style.opacity = '1';
                pmQtyContainer.style.pointerEvents = 'auto';
            }
            const pmQtyInputVal = document.getElementById('pmQtyInput');
            if (pmQtyInputVal) {
                if (parseInt(pmQtyInputVal.value) === 0) {
                    pmQtyInputVal.value = 1;
                }
                pmQtyInputVal.disabled = false;
            }
        }
    }

    if (pmDesc) {
        pmDesc.textContent = data.desc ||
            'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla nibh diam, blandit vel consequat nec, ultrices et ipsum. Nulla varius magna a consequat pulvinar.';
    }
    if (pmCategory) pmCategory.textContent = data.cat;
    if (pmTag) pmTag.textContent = data.cat + ', ' + data.name.split(' ')[0] + ', Cream, Chocolate';
    if (pmSku) pmSku.textContent = 'ZYH-' + data.id.padStart(4, '0');

    // Handle images
    if (thumbContainer) {
        thumbContainer.innerHTML = '';
        const images = [];
        if (data.f1) images.push(data.f1);
        if (data.f2) images.push(data.f2);
        if (data.f3) images.push(data.f3);
        if (data.f4) images.push(data.f4);

        if (images.length === 0) {
            images.push('https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop');
        }

        if (pmMainImage) pmMainImage.src = images[0];

        images.forEach((imgSrc, index) => {
            const div = document.createElement('div');
            div.className = 'pm-thumb' + (index === 0 ? ' active' : '');
            div.innerHTML = '<img src="' + imgSrc + '" alt="thumb">';
            div.onclick = function() {
                document.querySelectorAll('.pm-thumb').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                if (pmMainImage) pmMainImage.src = imgSrc;
            };
            thumbContainer.appendChild(div);
        });
    }

    const modal = document.getElementById('productModal');
    if (modal) modal.style.display = 'flex';
};

window.updateModalQty = function(delta) {
    const input = document.getElementById('pmQtyInput');
    if (input) {
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
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

window.addToCart = function(product) {
    const existing = window.cart.find(item => item.id === product.id);
    if (existing) {
        existing.qty += (product.qty || 1);
    } else {
        window.cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            img: product.img,
            qty: product.qty || 1
        });
    }
    window.saveCart();
    window.toggleCart(true);
};

window.updateQty = function(id, delta) {
    const item = window.cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty < 1) {
            window.removeFromCart(id);
        } else {
            window.saveCart();
            window.renderCart();
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
                <div class="cart-item-qty-wrap">
                    <button class="cart-qty-btn" onclick="window.updateQty('${item.id}', -1)"><i class="fa-solid fa-minus"></i></button>
                    <span>${item.qty}</span>
                    <button class="cart-qty-btn" onclick="window.updateQty('${item.id}', 1)"><i class="fa-solid fa-plus"></i></button>
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
        const storedAddress = conf.userAddress || "Alamat belum diatur";
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
        if (displayBillingAddress) displayBillingAddress.textContent = storedAddress;
        if (displayBillingEmail) displayBillingEmail.textContent = storedEmail;
        if (displayBillingPhone) displayBillingPhone.textContent = storedPhone;

        if (inputProfileName) inputProfileName.value = storedName;
        if (inputBillingAddress) inputBillingAddress.value = conf.userAddress || "";
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

            setTimeout(() => {
                Swal.close();
                Swal.fire({
                    title: 'Dibatalkan!',
                    text: 'Pesanan Anda telah berhasil dibatalkan.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                }).then(() => {
                    window.location.reload();
                });
            }, 1000);
        }
    });
};

window.selectCancelReason = function(el) {
    document.querySelectorAll('.cancel-reason-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
};

window.trackOrder = function(orderNumber) {
    Swal.fire({
        title: 'Pelacakan Pesanan',
        text: `Pesanan Anda ${orderNumber} saat ini sedang dalam proses pengiriman.`,
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: {
            popup: 'rounded-4'
        }
    });
};

window.cancelDummyOrder = function() {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        text: 'Apakah Anda yakin ingin membatalkan pesanan #DUMMY ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ea4b48',
        cancelButtonColor: '#c3cdd6',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const dummyOrderRow = document.getElementById('dummyOrderRow');
            const dummyCancelBtn = document.getElementById('dummyCancelBtn');
            if (dummyOrderRow) dummyOrderRow.style.opacity = '0.5';
            if (dummyCancelBtn) dummyCancelBtn.style.display = 'none';

            Swal.fire({
                title: 'Dibatalkan!',
                text: 'Pesanan dummy berhasil dibatalkan.',
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: {
                    popup: 'rounded-4'
                }
            });
        }
    });
};

window.cancelDummyRow = function(rowId) {
    window.cancelOrder(null, '#DUMMY');
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

    const firstName = document.getElementById('inputFirstName')?.value || '';
    const lastName = document.getElementById('inputLastName')?.value || '';
    const email = document.getElementById('inputAccountEmail')?.value || '';
    const phone = document.getElementById('inputAccountPhone')?.value || '';
    const fullName = (firstName + ' ' + lastName).trim();

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
                localStorage.setItem('zyh_cust_name', fullName);
                localStorage.setItem('zyh_cust_email', email);
                localStorage.setItem('zyh_cust_phone', phone);

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
            alamat: street
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
        const storedName = localStorage.getItem('zyh_cust_name') || conf.userNama;
        const storedAddress = localStorage.getItem('zyh_cust_address') || "pekanbaru, Rumbai, Umban Sari";
        const storedEmail = localStorage.getItem('zyh_cust_email') || conf.userEmail;
        const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) " + conf.userPhone;

        document.querySelectorAll('#displayBillingName').forEach(el => el.textContent = storedName);
        document.querySelectorAll('#displayBillingAddress').forEach(el => el.textContent = storedAddress);
        document.querySelectorAll('#displayBillingEmail').forEach(el => el.textContent = storedEmail);
        document.querySelectorAll('#displayBillingPhone').forEach(el => el.textContent = storedPhone);
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
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
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
            img: conf.productImg,
            qty: parseInt(document.getElementById('qtyInput')?.value || 1)
        });
        window.location.href = conf.checkoutRoute || "/checkout";
    }
};

window.buyCustomNow = function() {
    if (window.detailConfig) {
        const conf = window.detailConfig;
        const qty = parseInt(document.getElementById('qtyInput')?.value || 1);
        const formattedPrice = new Intl.NumberFormat('id-ID').format(conf.productPrice);
        const message = encodeURIComponent("Halo Zyh Corner, saya tertarik untuk memesan Custom Product: " + conf.productName + " (Harga: Rp. " + formattedPrice + ") sebanyak " + qty + " pcs. Mohon info lebih lanjut.");
        window.open("https://wa.me/628123456789?text=" + message, "_blank");
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
        <div class="cart-table-wrapper" style="overflow-x: auto; margin-bottom: 30px;">
            <table class="products-table" style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="background: #fdfcfb; border-bottom: 1.5px solid #f2f2f2; text-align: left;">
                        <th style="padding: 16px 20px; font-size: 13px; color: #888;">PRODUCT</th>
                        <th style="padding: 16px 20px; font-size: 13px; color: #888;">PRICE</th>
                        <th style="padding: 16px 20px; font-size: 13px; color: #888;">QUANTITY</th>
                        <th style="padding: 16px 20px; font-size: 13px; color: #888;">SUBTOTAL</th>
                        <th style="padding: 16px 20px;"></th>
                    </tr>
                </thead>
                <tbody>
                    ${cartItems.map(item => `
                        <tr style="border-bottom: 1px solid #f2f2f2;">
                            <td style="padding: 20px;">
                                <div class="product-cell">
                                    <div class="product-img-wrap">
                                        <img src="${item.img}" alt="${item.name}">
                                    </div>
                                    <span class="product-title" style="font-weight: 500;">${item.name}</span>
                                </div>
                            </td>
                            <td style="padding: 20px; font-weight: 500;">Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}</td>
                            <td style="padding: 20px;">
                                <div class="qty-box" style="width: 110px; height: 36px; border: 1.5px solid #e6e6e6; border-radius: 50px;">
                                    <button class="qty-btn" onclick="window.updateQty('${item.id}', -1)" style="width: 32px; height: 100%; font-size: 12px;">-</button>
                                    <input type="text" class="qty-input" value="${item.qty}" readonly style="width: 40px; font-size: 13px;">
                                    <button class="qty-btn" onclick="window.updateQty('${item.id}', 1)" style="width: 32px; height: 100%; font-size: 12px;">+</button>
                                </div>
                            </td>
                            <td style="padding: 20px; font-weight: 600; color: #1a1a1a;">
                                Rp. ${new Intl.NumberFormat('id-ID').format(item.price * item.qty)}
                            </td>
                            <td style="padding: 20px; text-align: right;">
                                <i class="fa-solid fa-xmark" onclick="window.removeFromCart('${item.id}')" style="cursor: pointer; color: #999; font-size: 16px;"></i>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <div class="summary-card" style="width: 380px; padding: 24px; border: 1px solid #e6e6e6; border-radius: 8px;">
                <h3 class="summary-card-title" style="font-size: 14px; margin-bottom: 20px;">Cart Totals</h3>
                <div class="summary-calc-list">
                    <div class="summary-calc-row">
                        <span>Subtotal</span>
                        <span style="font-weight: 500; color: #1a1a1a;">Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                    <div class="summary-calc-row">
                        <span>Shipping</span>
                        <span style="font-weight: 500; color: #1a1a1a;">Free</span>
                    </div>
                    <div class="summary-calc-row main-total" style="border-top: 1.5px solid #f2f2f2; padding-top: 15px; margin-top: 10px;">
                        <span>Total</span>
                        <span>Rp. ${new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                    </div>
                </div>
                <a href="${window.layoutConfig?.checkoutRoute || '/checkout'}" class="btn-tracking-solid" style="width: 100%; text-align: center; margin-top: 20px; padding: 14px 20px; box-sizing: border-box; display: block; border-radius: 50px;">Proceed to Checkout</a>
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

    // Del Institute coordinates as starting point
    const initialCoords = [2.3833, 99.1500];
    
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.warn('Leaflet map library is not loaded.');
        return;
    }
    
    checkoutMap = L.map('map', {
        scrollWheelZoom: false
    }).setView(initialCoords, 15);

    // Add premium styling layer from OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(checkoutMap);

    // Create draggable marker
    checkoutMarker = L.marker(initialCoords, {
        draggable: true
    }).addTo(checkoutMap);

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
    if (latEl) latEl.value = lat;
    if (lngEl) lngEl.value = lng;
    if (displayEl) {
        displayEl.innerHTML = `Lokasi Terpilih: (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
    }
};

window.renderOrderSummaryCheckout = function() {
    const orderSummaryDiv = document.getElementById('orderItemsSummary');
    if (!orderSummaryDiv) return;

    let localCart = JSON.parse(localStorage.getItem('zyh_cart')) || [];
    
    if (localCart.length === 0) {
        orderSummaryDiv.innerHTML = '<p style="color:#999; font-style:italic;">Keranjang kosong</p>';
        window.location.href = window.checkoutConfig?.userChartRoute || "/cart";
        return;
    }

    let summaryRowsHtml = localCart.map(item => {
        let priceSub = item.price * item.qty;
        let priceSubFormatted = 'Rp. ' + new Intl.NumberFormat('id-ID').format(priceSub);
        
        return `
            <div class="order-item-row" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f2f2f2;">
                <div class="order-item-left" style="display: flex; align-items: center; gap: 10px; width: 70%;">
                    <div class="order-item-img" style="width: 45px; height: 45px; border-radius: 4px; overflow: hidden; border: 1px solid #e6e6e6; background: #f9f9f9; display: flex; align-items: center; justify-content: center; padding: 2px; flex-shrink: 0;">
                        <img src="${item.img}" alt="${item.name}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div class="order-item-info" style="display: flex; flex-direction: column; overflow: hidden;">
                        <span class="order-item-name" title="${item.name}" style="font-size: 13.5px; font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">${item.name}</span>
                        <span class="order-item-qty" style="font-size: 12px; color: #666;">Qty: ${item.qty}</span>
                    </div>
                </div>
                <span class="order-item-price" style="font-size: 13.5px; font-weight: 600; color: #1a1a1a;">${priceSubFormatted}</span>
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
        if (transferSub) transferSub.style.display = 'flex';
    } else if (method === 'ewallet' && ewalletRadio) {
        ewalletRadio.checked = true;
        const parent = ewalletRadio.closest('.payment-option-item');
        if (parent) parent.classList.add('active');
        if (ewalletSub) ewalletSub.style.display = 'flex';
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
        let streetAddress = addrEl ? addrEl.value : '';
        let notes = notesEl ? notesEl.value : '';
        
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
                shipping_method: shippingVal
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
});

