<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Services\GeoLocationService;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Customer;

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
    public function getCleanNotesAttribute(): string
    {
        $raw = $this->catatan_pesanan ?? '';
        $clean = preg_replace('/\[Penerima:\s*[^()]*\([^)]+\)\]/i', '', $raw);
        $clean = preg_replace('/\[Titik GPS:\s*[^\]]+\]/i', '', $clean);
        return trim($clean);
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        if (!empty($this->catatan_pesanan) && preg_match('/\[Penerima:\s*[^()]*\(([^)]+)\)\]/i', $this->catatan_pesanan, $m)) {
            return trim($m[1]);
        }
        if (!empty($this->email_pembeli)) {
            $user = User::where('email', $this->email_pembeli)->first();
            if ($user && !empty($user->no_hp)) {
                return $user->no_hp;
            }
            $customer = Customer::where('email', $this->email_pembeli)->first();
            if ($customer && !empty($customer->telepon)) {
                return $customer->telepon;
            }
        }
        return null;
    }

    public function getResolvedAddressAttribute(): array
    {
        $data = [
            'street' => $this->alamat_pengiriman,
            'label' => null,
            'area' => null,
            'postal_code' => null,
            'notes' => null,
            'gps' => null,
            'maps_url' => null,
            'detected_place' => null,
            'detected_road' => null,
            'is_dummy_replaced' => false,
            'recipient_phone' => $this->customer_phone,
            'clean_notes' => $this->clean_notes,
        ];

        // 1. Extract GPS from catatan_pesanan if available
        if (!empty($this->catatan_pesanan) && preg_match('/\[Titik GPS:\s*([-\d.]+),\s*([-\d.]+)(?:\s*\|\s*([^\]]+))?\]/i', $this->catatan_pesanan, $m)) {
            $data['gps'] = $m[1] . ', ' . $m[2];
            if (!empty($m[3]) && str_starts_with(trim($m[3]), 'http')) {
                $data['maps_url'] = trim($m[3]);
            }
        }

        // 2. If user exists, inspect UserAddress
        if (empty($data['gps']) && !empty($this->customer_name)) {
            $matchedUser = null;
            if (!empty($this->email_pembeli)) {
                $matchedUser = User::where('email', $this->email_pembeli)->first();
            }
            if (!$matchedUser) {
                $matchedUser = User::where('nama', $this->customer_name)->first();
            }
            if ($matchedUser) {
                $userAddr = UserAddress::where('user_id', $matchedUser->user_id)->orderByDesc('is_default')->first();
                if ($userAddr) {
                    $data['label'] = $userAddr->label;
                    if (empty($data['street'])) {
                        $data['street'] = $userAddr->street_address;
                    }
                    $data['area'] = $userAddr->destination_area_name;
                    $data['postal_code'] = $userAddr->postal_code;
                    $data['notes'] = $userAddr->notes;
                    if ($userAddr->latitude && $userAddr->longitude) {
                        $data['gps'] = $userAddr->latitude . ', ' . $userAddr->longitude;
                    }
                }
            }
        }

        // 3. Fallback maps URL if GPS is present
        if (!empty($data['gps']) && empty($data['maps_url'])) {
            $data['maps_url'] = 'https://maps.google.com/?q=' . urlencode($data['gps']);
        }

        // 4. Reverse Geocode via GeoLocationService
        if (!empty($data['gps'])) {
            $coords = explode(',', (string) $data['gps']);
            if (count($coords) === 2) {
                $lat = trim($coords[0]);
                $lng = trim($coords[1]);
                $geoInfo = GeoLocationService::reverseGeocode($lat, $lng);
                if ($geoInfo) {
                    $data['detected_place'] = $geoInfo['place_name'];
                    $data['detected_road'] = $geoInfo['road'];

                    $cleanStreet = trim(preg_replace('/\(Kode Pos:.*?\)/i', '', (string)$data['street']));
                    $isDummyStreet = false;
                    if (empty($cleanStreet) || (strlen($cleanStreet) <= 8 && preg_match('/^[a-z0-9_-]+$/i', $cleanStreet))) {
                        $isDummyStreet = true;
                    }

                    if ($isDummyStreet) {
                        $resolvedParts = array_filter([$geoInfo['place_name'], $geoInfo['road']]);
                        if (!empty($resolvedParts)) {
                            $data['street'] = implode(', ', $resolvedParts);
                            $data['is_dummy_replaced'] = true;
                        } elseif (!empty($geoInfo['display_name'])) {
                            $data['street'] = $geoInfo['display_name'];
                            $data['is_dummy_replaced'] = true;
                        }
                    }

                    if (empty($data['area']) && !empty($geoInfo['area'])) {
                        $data['area'] = $geoInfo['area'];
                    }
                    if (empty($data['postal_code']) && !empty($geoInfo['postal_code'])) {
                        $data['postal_code'] = $geoInfo['postal_code'];
                    }
                }
            }
        }

        if (empty($data['postal_code']) && !empty($data['street'])) {
            if (preg_match('/(?:Kode Pos:\s*|,\s*)(\d{5})/i', $data['street'], $matches)) {
                $data['postal_code'] = $matches[1];
            }
        }

        return $data;
    }

    protected $casts = [
        'items' => 'array',
        'bukti_pembayaran_uploaded_at' => 'datetime',
    ];
}
