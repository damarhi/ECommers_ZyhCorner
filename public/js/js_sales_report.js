function exportToGoogleSpreadsheet() {
            const gsheetUrl = "https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing";
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('format', 'raw');

            const exportBase = (window.salesReportConfig && window.salesReportConfig.exportUrl) || "/admin/sales/export";
            const exportUrl = exportBase + "?" + currentParams.toString();

            Swal.fire({
                title: 'Menyiapkan Data Laporan Terfilter...',
                text: 'Mengolah data transaksi, kadaluarsa, dan retur untuk Google Spreadsheet',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(exportUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData && resData.data) {
                    navigator.clipboard.writeText(resData.data).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Laporan Terfilter Berhasil Disalin!',
                            html: 'Seluruh data laporan terfilter telah otomatis disalin ke clipboard.<br><br><b>Langkah Selanjutnya:</b><br>1. Klik tombol <b>"Buka Google Spreadsheet"</b> di bawah.<br>2. Klik sel <b>A1</b> di Google Spreadsheet lalu tekan <b>Ctrl + V</b> (Paste).',
                            confirmButtonText: '<i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Google Spreadsheet',
                            confirmButtonColor: '#0f9d58',
                            showCancelButton: true,
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(gsheetUrl, '_blank');
                            }
                        });
                    }).catch(() => {
                        window.open(gsheetUrl, '_blank');
                    });
                } else {
                    window.open(gsheetUrl, '_blank');
                }
            })
            .catch(() => {
                window.open(gsheetUrl, '_blank');
            });
        }
