let currentStockModalValue = 0;
    let addStockModalInstance = null;

    function openAddStockModal(id, name, currentStock, expiryDate, image) {
        document.getElementById('stock_product_id').value = id;
        document.getElementById('stock_product_name').innerText = name;
        document.getElementById('stock_product_img').src = image || (window.posConfig && window.posConfig.fallbackImg) || '/image/logo_circle.png';
        
        currentStockModalValue = parseInt(currentStock) || 0;
        let badgeEl = document.getElementById('stock_current_badge');
        if (currentStockModalValue <= 0) {
            badgeEl.className = 'badge bg-danger';
            badgeEl.innerText = '0 Pcs (Habis)';
        } else {
            badgeEl.className = 'badge bg-success';
            badgeEl.innerText = currentStockModalValue + ' Pcs';
        }

        document.getElementById('additional_stock').value = 10;
        document.getElementById('stock_tanggal_expired').value = expiryDate ? expiryDate.substring(0, 10) : '';
        calculateNewStock();

        if (!addStockModalInstance) {
            addStockModalInstance = new bootstrap.Modal(document.getElementById('addStockModal'));
        }
        addStockModalInstance.show();
    }

    function setQuickStock(val) {
        document.getElementById('additional_stock').value = val;
        calculateNewStock();
    }

    function calculateNewStock() {
        let addVal = parseInt(document.getElementById('additional_stock').value) || 0;
        let newTotal = Math.max(0, currentStockModalValue + addVal);
        document.getElementById('estimated_new_stock').innerText = newTotal + ' Pcs';
    }

    function submitAddStock(event) {
        event.preventDefault();
        let productId = document.getElementById('stock_product_id').value;
        let additionalStock = parseInt(document.getElementById('additional_stock').value) || 0;
        let expiryDate = document.getElementById('stock_tanggal_expired').value;
        let submitBtn = document.getElementById('btn-submit-stock');

        if (additionalStock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Jumlah Tidak Valid',
                text: 'Jumlah tambahan stok minimal 1 item.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';

        fetch('/admin/pos/products/' + productId + '/add-stock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (window.posConfig && window.posConfig.csrfToken) || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : ''),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                additional_stock: additionalStock,
                tanggal_expired: expiryDate || null
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Simpan Stok';
            
            if (data.success) {
                if (addStockModalInstance) {
                    addStockModalInstance.hide();
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Stok Berhasil Ditambahkan!',
                    text: data.message || 'Stok produk berhasil diperbarui.',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menambah Stok',
                    text: data.message || 'Terjadi kesalahan pada server.',
                    confirmButtonColor: '#ea4b48'
                });
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle-outline me-1"></i> Simpan Stok';
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Gagal terhubung ke server.',
                confirmButtonColor: '#ea4b48'
            });
        });
    }

    let cart = {};
    function saveCartToStorage() {
        try {
            localStorage.setItem('zyh_pos_cart', JSON.stringify(cart));
        } catch (e) {
            console.warn('LocalStorage error:', e);
        }
    }

    function loadCartFromStorage() {
        try {
            const saved = localStorage.getItem('zyh_pos_cart');
            if (saved) {
                cart = JSON.parse(saved) || {};
            }
        } catch (e) {
            cart = {};
        }
    }

    function toggleCart() {
        document.getElementById('cart-sidebar').classList.toggle('open');
    }

    function addToCart(id, name, price, image, maxStock, originalPrice) {
        if (maxStock !== undefined && maxStock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Habis',
                text: 'Produk ini sudah habis dan tidak dapat ditambahkan.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (!cart[id]) {
            cart[id] = {
                id: id,
                id_produk: id,
                name: name,
                price: price,
                originalPrice: originalPrice || price,
                quantity: 0,
                qty: 0,
                image: image,
                maxStock: (maxStock !== undefined) ? parseInt(maxStock) : 999
            };
        } else if (originalPrice && !cart[id].originalPrice) {
            cart[id].originalPrice = originalPrice;
        }

        if (cart[id].maxStock !== undefined && cart[id].quantity >= cart[id].maxStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Stok Tercapai',
                text: `Maksimal stok yang tersedia hanya ${cart[id].maxStock} item.`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        cart[id].quantity += 1;
        cart[id].qty = cart[id].quantity;
        saveCartToStorage();
        updateCartUI();
    }

    function removeFromCart(id) {
        if (cart[id] && cart[id].quantity > 0) {
            cart[id].quantity -= 1;
            cart[id].qty = cart[id].quantity;
            if (cart[id].quantity === 0) {
                delete cart[id];
            }
            saveCartToStorage();
            updateCartUI();
        }
    }

    function updateCartUI() {
        let totalItems = 0;
        let totalPrice = 0;
        let html = '';
        
        for (let id in cart) {
            let item = cart[id];
            totalItems += item.quantity;
            totalPrice += (item.price * item.quantity);
            
            html += `
                <div class="d-flex mb-4 position-relative">
                    <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-1 text-muted" style="font-size:13px;">${item.name}</h6>
                        <div class="d-flex align-items-center">
                            <span class="text-muted" style="font-size: 12px;">${item.quantity} x</span> 
                            <strong class="ms-1" style="font-size: 13px;">Rp. ${item.price.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn-qty" onclick="removeFromCart(${id})"><i class="mdi mdi-minus"></i></button>
                        <span class="mx-2" style="font-size: 14px;">${item.quantity}</span>
                        <button class="btn-qty" onclick="addToCart(${id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}', ${item.maxStock || 999}, ${item.originalPrice || item.price})"><i class="mdi mdi-plus"></i></button>
                        <button class="btn btn-sm text-muted p-1 ms-2" onclick="delete cart[${id}]; saveCartToStorage(); updateCartUI();" style="background:transparent; border:none;">
                            <i class="mdi mdi-close-circle-outline" style="font-size: 18px;"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('cart-items').innerHTML = html || '<div class="text-center py-5 text-muted"><i class="mdi mdi-cart-outline mb-2" style="font-size: 48px;"></i><p class="mb-0">Keranjang masih kosong</p></div>';
        document.getElementById('total-items').innerText = totalItems;
        document.getElementById('cart-count').innerText = totalItems;
        document.getElementById('cart-total-items-bottom').innerText = totalItems;
        
        let priceFormatted = 'Rp. ' + totalPrice.toLocaleString('id-ID');
        document.getElementById('total-price').innerText = priceFormatted;
        document.getElementById('cart-total-price-bottom').innerText = priceFormatted;
    }

    function showCheckout() {
        if(Object.keys(cart).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        window.location.href = (window.posConfig && window.posConfig.checkoutRoute) || '/admin/pos/checkout';
    }
    document.addEventListener("DOMContentLoaded", function() {
        loadCartFromStorage();
        updateCartUI();
    });
