<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $fillable = [
        'customer_name', 'order_id', 'reason', 'return_date', 'product_name', 'status', 'catatan_admin', 'foto_retur'
    ];

    /**
     * Standardize status values on save
     */
    public function setStatusAttribute($value): void
    {
        $clean = strtolower(trim((string) $value));
        $map = [
            'completed'  => 'Done',
            'selesai'    => 'Done',
            'done'       => 'Done',
            'processing' => 'Processed',
            'diproses'   => 'Processed',
            'processed'  => 'Processed',
            'accepted'   => 'Accepted',
            'diterima'   => 'Accepted',
            'disetujui'  => 'Accepted',
            'rejected'   => 'Rejected',
            'ditolak'    => 'Rejected',
            'new'        => 'New',
            'baru'       => 'New',
        ];

        $this->attributes['status'] = $map[$clean] ?? ucfirst($value);
    }

    /**
     * Badge CSS class for admin UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match (strtolower($this->status ?? '')) {
            'accepted'  => 'badge-soft-success',
            'done'      => 'badge-soft-dark',
            'processed' => 'badge-soft-info',
            'rejected'  => 'badge-soft-danger',
            default     => 'badge-soft-warning',
        };
    }

    /**
     * Indonesian translated label
     */
    public function getStatusLabelAttribute(): string
    {
        return match (strtolower($this->status ?? '')) {
            'accepted'  => 'Diterima',
            'done'      => 'Selesai',
            'processed' => 'Diproses',
            'rejected'  => 'Ditolak',
            default     => 'Baru',
        };
    }

    /**
     * Alias accessors for backward compatibility and Indonesian attributes
     */
    public function getNamaPelangganAttribute(): ?string
    {
        return $this->customer_name;
    }

    public function getOrderNumberAttribute(): ?string
    {
        return $this->order_id;
    }

    public function getAlasanAttribute(): ?string
    {
        return $this->reason;
    }

    public function getStatusReturAttribute(): ?string
    {
        return $this->status;
    }
}
