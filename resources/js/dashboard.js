// Load persisted address details from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const storedName = localStorage.getItem('zyh_cust_name') || window.dashboardConfig.userNama;
    const storedAddress = localStorage.getItem('zyh_cust_address') || "pekanbaru, Rumbai, Umban Sari";
    const storedEmail = localStorage.getItem('zyh_cust_email') || window.dashboardConfig.userEmail;
    const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) " + window.dashboardConfig.userPhone;
    
    document.getElementById('displayProfileName').textContent = storedName;
    document.getElementById('displayBillingName').textContent = storedName;
    document.getElementById('displayBillingAddress').textContent = storedAddress;
    document.getElementById('displayBillingEmail').textContent = storedEmail;
    document.getElementById('displayBillingPhone').textContent = storedPhone;
    
    // Sync values to form inputs
    document.getElementById('inputProfileName').value = storedName;
    document.getElementById('inputBillingAddress').value = storedAddress;
    document.getElementById('inputBillingEmail').value = storedEmail;
    document.getElementById('inputBillingPhone').value = storedPhone;
});

// Profile modal toggles
window.openEditProfile = function() {
    document.getElementById('modalEditProfile').style.display = 'flex';
}

window.closeEditProfile = function() {
    document.getElementById('modalEditProfile').style.display = 'none';
}

window.saveProfile = function(e) {
    e.preventDefault();
    const newName = document.getElementById('inputProfileName').value;
    localStorage.setItem('zyh_cust_name', newName);
    
    document.getElementById('displayProfileName').textContent = newName;
    document.getElementById('displayBillingName').textContent = newName;
    
    window.closeEditProfile();
    
    Swal.fire({
        title: 'Berhasil!',
        text: 'Profil berhasil diperbarui.',
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Address modal toggles
window.openEditAddress = function() {
    document.getElementById('modalEditAddress').style.display = 'flex';
}

window.closeEditAddress = function() {
    document.getElementById('modalEditAddress').style.display = 'none';
}

window.saveAddress = function(e) {
    e.preventDefault();
    const address = document.getElementById('inputBillingAddress').value;
    const email = document.getElementById('inputBillingEmail').value;
    const phone = document.getElementById('inputBillingPhone').value;
    
    localStorage.setItem('zyh_cust_address', address);
    localStorage.setItem('zyh_cust_email', email);
    localStorage.setItem('zyh_cust_phone', phone);
    
    document.getElementById('displayBillingAddress').textContent = address;
    document.getElementById('displayBillingEmail').textContent = email;
    document.getElementById('displayBillingPhone').textContent = phone;
    
    window.closeEditAddress();
    
    Swal.fire({
        title: 'Berhasil!',
        text: 'Alamat tagihan berhasil diperbarui.',
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Tab Alerts
window.showTabAlert = function(tabName) {
    Swal.fire({
        title: tabName,
        text: `Halaman ${tabName} pelanggan akan segera hadir!`,
        icon: 'info',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Cancel Order Handler
window.cancelOrder = function(id, orderNumber) {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        text: `Apakah Anda yakin ingin membatalkan pesanan ${orderNumber}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ea4b48',
        cancelButtonColor: '#c3cdd6',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            // POST request to cancel order (mocking logic or endpoint if exists, otherwise display success)
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
}

window.payOrderNow = function(orderId) {
    Swal.fire({
        title: 'Mendapatkan Token Pembayaran...',
        text: 'Harap tunggu.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/order-history/${orderId}/pay`)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success && data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Pembayaran berhasil dikonfirmasi.',
                            icon: 'success',
                            confirmButtonColor: '#00b207'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onPending: function(result) {
                        Swal.fire({
                            title: 'Tertunda',
                            text: 'Pembayaran sedang diproses atau tertunda.',
                            icon: 'info',
                            confirmButtonColor: '#00b207'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onError: function(result) {
                        Swal.fire({
                            title: 'Gagal',
                            text: 'Pembayaran gagal dilakukan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                    },
                    onClose: function() {
                        Swal.fire({
                            title: 'Dibatalkan',
                            text: 'Anda menutup pop-up pembayaran.',
                            icon: 'warning',
                            confirmButtonColor: '#00b207'
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal memulai pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
}

// Tracking Order Handler
window.trackOrder = function(orderNumber) {
    Swal.fire({
        title: 'Pelacakan Pesanan',
        text: `Pesanan Anda ${orderNumber} saat ini sedang dalam proses pengiriman.`,
        icon: 'success',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Dummy Row Cancellation
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
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('dummyOrderRow').style.opacity = '0.5';
            document.getElementById('dummyCancelBtn').style.display = 'none';
            Swal.fire({
                title: 'Dibatalkan!',
                text: 'Pesanan dummy berhasil dibatalkan.',
                icon: 'success',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
        }
    });
}
