<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $fillable = [
        'order_number',
        'jenis_penjualan',
        'payment_method',
        'shipping_method',
        'customer_name',
        'email_pembeli',
        'alamat_pengiriman',
        'catatan_pesanan',
        'payment_status',
        'snap_token',
        'order_status',
        'total',
        'items',
        'cancel_reason',
        'bukti_pembayaran',
        'bukti_pembayaran_uploaded_at',
        'catatan_verifikasi_pembayaran',
    ];

    public function getIsCanceledAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['batal', 'cancel', 'failed']);
    }

    public function getIsCompletedAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['selesai', 'completed', 'delivered']);
    }

    public function getIsShippingAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['otw', 'way', 'kirim', 'sedang dikirim']);
    }

    public function getIsProcessingAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['proses', 'processing', 'ready', 'dikemas']);
    }

    public function getIsPendingAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['menunggu pembayaran', 'pending']) && !\Illuminate\Support\Str::contains($status, ['konfirmasi']);
    }

    public function getIsAwaitingConfirmationAttribute(): bool
    {
        $status = strtolower($this->order_status ?? '');
        return \Illuminate\Support\Str::contains($status, ['menunggu konfirmasi', 'unconfirmed']);
    }

    public function getCanBeCanceledAttribute(): bool
    {
        return $this->is_pending || $this->is_awaiting_confirmation;
    }

    public function getStatusStepAttribute(): int
    {
        if ($this->is_canceled) return 0;
        if ($this->is_completed) return 5;
        if ($this->is_shipping) return 4;
        if ($this->is_processing) return 3;

        // Step 2: Verifikasi Pembayaran (Midtrans lunas atau metode COD terverifikasi)
        $isPaid = strtoupper($this->payment_status ?? '') === 'PAID';
        $isCod = strtoupper($this->payment_method ?? '') === 'COD';

        if ($isPaid || $isCod) {
            return 2;
        }

        return 1;
    }

    public function getCourierIconAttribute(): string
    {
        $sm = strtolower($this->shipping_method ?? '');
        if (\Illuminate\Support\Str::contains($sm, ['jne', 'sicepat', 'j&t', 'antar', 'reguler'])) {
            return 'fa-truck';
        }
        if (\Illuminate\Support\Str::contains($sm, ['ambil', 'pickup', 'pick up'])) {
            return 'fa-shop';
        }
        return 'fa-motorcycle';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_canceled) return 'Dibatalkan';
        if ($this->is_completed) return 'Selesai';
        if ($this->is_shipping) return 'Dalam Perjalanan';
        if ($this->is_processing) return 'Sedang Dikemas';
        if ($this->is_awaiting_confirmation) return 'Menunggu Konfirmasi';
        if ($this->is_pending) return 'Menunggu Pembayaran';
        return ucfirst($this->order_status ?? 'Diproses');
    }

    public function getStatusClassAttribute(): string
    {
        if ($this->is_canceled) return 'cancelled';
        if ($this->is_completed) return 'completed';
        if ($this->is_shipping) return 'on_the_way';
        if ($this->is_processing) return 'processing';
        if ($this->is_awaiting_confirmation) return 'awaiting_confirmation';
        if ($this->is_pending) return 'pending';
        return 'pending';
    }

    public function getFormattedCourierNameAttribute(): string
    {
        return $this->shipping_method ?: 'Kurir Toko / Pengiriman Standar';
    }

    public function getResiCodeAttribute(): string
    {
        if (!empty($this->no_resi)) {
            return (string) $this->no_resi;
        }
        return 'BS-' . strtoupper(str_replace('#', '', (string)$this->order_number));
    }

    public function getItemsSubtotalAttribute(): float
    {
        $items = $this->items;
        if (!is_array($items)) return 0.0;
        $subtotal = 0.0;
        foreach ($items as $item) {
            $qty = isset($item['qty']) ? intval($item['qty']) : 1;
            $price = isset($item['price']) ? floatval($item['price']) : 0.0;
            $subtotal += ($price * $qty);
        }
        return $subtotal;
    }

    public function getShippingCostAttribute(): float
    {
        $total = floatval($this->total ?? 0);
        $subtotal = $this->items_subtotal;
        return max(0.0, $total - $subtotal);
    }
    public function getHasPaymentProofAttribute(): bool
    {
        return !empty($this->bukti_pembayaran);
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (empty($this->bukti_pembayaran)) {
            return null;
        }
        if (str_starts_with($this->bukti_pembayaran, 'http://') || str_starts_with($this->bukti_pembayaran, 'https://')) {
            return $this->bukti_pembayaran;
        }
        return asset('storage/' . ltrim($this->bukti_pembayaran, '/'));
    }

    public function getIsBankTransferAttribute(): bool
    {
        $method = strtoupper((string) ($this->payment_method ?? ''));
        return str_contains($method, 'TRANSFER') || str_contains($method, 'BANK');
    }

    public function getIsPaymentProofRejectedAttribute(): bool
    {
        if (empty($this->catatan_verifikasi_pembayaran)) {
            return false;
        }
        return str_contains(strtolower($this->catatan_verifikasi_pembayaran), 'ditolak') && empty($this->bukti_pembayaran);
    }

    public function getPaymentProofRejectionReasonAttribute(): ?string
    {
        if (!$this->is_payment_proof_rejected) {
            return null;
        }
        $text = $this->catatan_verifikasi_pembayaran;
        // Format biasanya: "Bukti ditolak: <alasan> (<timestamp> WIB)"
        if (preg_match('/Bukti ditolak:\s*(.*?)(?:\s*\([^\)]+\))?$/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return $text;
    }
    protected $casts = [
        'items' => 'array',
        'bukti_pembayaran_uploaded_at' => 'datetime',
    ];
}
