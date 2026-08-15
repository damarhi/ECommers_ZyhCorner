@extends('Layout.layout_pAdmin')

@section('title', 'Pesanan Online - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-format-list-bulleted"></i>
            </span>
            <span class="text-nowrap">Pesanan Online</span>
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <button type="button" class="btn btn-gradient-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="mdi mdi-plus me-1"></i> Tambah Pesanan
            </button>
        </div>
    </div>



    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <!-- Toolbar -->
                    <form method="GET" action="{{ route('orders.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                    <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter</span>
                                </button>
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Pembayaran</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="payment_status[]" value="Pending" class="form-check-input" {{ is_array(request('payment_status')) && in_array('Pending', request('payment_status')) ? 'checked' : '' }}> Tertunda
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="payment_status[]" value="Paid" class="form-check-input" {{ is_array(request('payment_status')) && in_array('Paid', request('payment_status')) ? 'checked' : '' }}> Dibayar
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                                </div>
                            </div>
                            <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nomor pesanan / pelanggan..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                        </div>
                        </div>
                        <button type="button" id="btnBulkDelete" class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center align-self-end align-self-md-center px-3" style="height: 38px;" title="Hapus Terpilih">
                            <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </form>

                    <form action="{{ route('orders.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                        @csrf
                        <div id="bulkDeleteInputs"></div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table" style="border-bottom: 1px solid #e4e4e4;">
                            <thead>
                                <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                                     <th style="padding: 15px 10px; width: 40px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" id="checkAllOrders" class="form-check-input" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Tanggal</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pelanggan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status Pembayaran</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Total</th>
                                    <th style="padding: 15px 10px; width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                     <td style="padding: 15px 10px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="ids[]" value="{{ $order->id }}" class="form-check-input order-row-checkbox" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </td>
                                    <td style="padding: 15px 10px; font-weight: 500; font-size: 14px; color: #2c2c2c;">{{ $order->order_number }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $order->created_at->format('M j, g:i A') }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $order->customer_name }}</td>
                                    <td style="padding: 15px 10px;">
                                        @if($order->payment_status == 'Paid')
                                            <span class="badge badge-soft-success">Dibayar</span>
                                        @else
                                            <span class="badge badge-soft-warning">Tertunda</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        @if($order->order_status == 'menunggu pembayaran')
                                            <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                        @elseif($order->order_status == 'dikemas')
                                            <span class="badge badge-soft-info">Dikemas</span>
                                        @elseif($order->order_status == 'dikirim')
                                            <span class="badge badge-soft-primary">Dikirim</span>
                                        @elseif($order->order_status == 'selesai')
                                            <span class="badge badge-soft-success">Selesai</span>
                                        @else
                                            <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td style="padding: 15px 10px;">
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-outline-success bg-white d-inline-flex align-items-center justify-content-center me-2" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;" title="Detail Pesanan">
                                                <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info bg-white d-inline-flex align-items-center justify-content-center me-2" data-bs-toggle="modal" data-bs-target="#editOrderModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;" title="Edit Pesanan">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-primary bg-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;" title="Hapus Pesanan">
                                                    <i class="mdi mdi-delete-outline" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-cart-outline text-muted mb-2" style="font-size: 64px;"></i>
                                            <h5 class="text-muted font-weight-bold">Belum Ada Pesanan</h5>
                                            <p class="text-muted mb-0" style="font-size: 14px;">Oops, pesanan pelanggan belum ada yang masuk nih.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modals (placed outside the table to prevent HTML rendering issues) -->
                    @foreach($orders as $order)
                        <!-- Edit Order Modal -->
                        <div class="modal fade" id="editOrderModal{{ $order->id }}" tabindex="-1" aria-labelledby="editOrderModalLabel{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 8px;">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title font-weight-bold" id="editOrderModalLabel{{ $order->id }}">Edit Pesanan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('orders.update', $order->id) }}" method="POST" class="edit-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-500 mb-2">Nama Pelanggan</label>
                                                <input type="text" class="form-control" name="customer_name" value="{{ $order->customer_name }}" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-500 mb-2">Total (Rp)</label>
                                                <input type="number" class="form-control" name="total" value="{{ $order->total }}" required min="0">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-500 mb-2">Status Pembayaran</label>
                                                <select class="form-select form-control" name="payment_status" required>
                                                    <option value="Pending" {{ $order->payment_status == 'Pending' ? 'selected' : '' }}>Tertunda</option>
                                                    <option value="Paid" {{ $order->payment_status == 'Paid' ? 'selected' : '' }}>Dibayar</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-500 mb-2">Status Pesanan</label>
                                                <select class="form-select form-control" name="order_status" required>
                                                    <option value="menunggu pembayaran" {{ $order->order_status == 'menunggu pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                                    <option value="dikemas" {{ $order->order_status == 'dikemas' ? 'selected' : '' }}>Dikemas</option>
                                                    <option value="dikirim" {{ $order->order_status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                                    <option value="selesai" {{ $order->order_status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-gradient-primary">Perbarui Pesanan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Order Modal -->
                        @include('Admin.kelola_penjualan.order_detail_modal', ['order' => $order])
                    @endforeach

                    <!-- Pagination -->
                    <!-- Pagination -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                        <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                            {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                            {{ $orders->total() }} Hasil
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addOrderModalLabel">Tambah Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Nama Pelanggan</label>
                        <input type="text" class="form-control" name="customer_name" placeholder="Masukkan nama pelanggan" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Total (Rp)</label>
                        <input type="number" class="form-control" name="total" placeholder="0" required min="0">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Status Pembayaran</label>
                        <select class="form-select form-control" name="payment_status" required>
                            <option value="Pending">Tertunda</option>
                            <option value="Paid">Dibayar</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-500 mb-2">Status Pesanan</label>
                        <select class="form-select form-control" name="order_status" required>
                            <option value="menunggu pembayaran">Menunggu Pembayaran</option>
                            <option value="dikemas">Dikemas</option>
                            <option value="dikirim">Dikirim</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkAll = document.getElementById('checkAllOrders');
        const rowCheckboxes = document.querySelectorAll('.order-row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.order-row-checkbox:checked').length;
            if (selectedCountSpan) selectedCountSpan.textContent = checkedCount;

            if (btnBulkDelete) {
                if (checkedCount > 0) {
                    btnBulkDelete.classList.remove('d-none');
                    btnBulkDelete.classList.add('d-inline-flex');
                } else {
                    btnBulkDelete.classList.add('d-none');
                    btnBulkDelete.classList.remove('d-inline-flex');
                }
            }

            if (checkAll) {
                checkAll.checked = rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateBulkDeleteState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteState);
        });

        if (btnBulkDelete && bulkDeleteForm) {
            btnBulkDelete.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedCheckboxes = document.querySelectorAll('.order-row-checkbox:checked');
                const count = selectedCheckboxes.length;
                if (count === 0) return;

                Swal.fire({
                    title: 'Hapus Pesanan Terpilih?',
                    text: `Apakah Anda yakin ingin menghapus ${count} data pesanan yang dipilih?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus Terpilih!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const inputsContainer = document.getElementById('bulkDeleteInputs');
                        inputsContainer.innerHTML = '';
                        selectedCheckboxes.forEach(cb => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = cb.value;
                            inputsContainer.appendChild(input);
                        });
                        bulkDeleteForm.submit();
                    }
                });
            });
        }

        @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any() && old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addOrderModal'));
            myModal.show();
        @endif
    });
</script>

@endsection
