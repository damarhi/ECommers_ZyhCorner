<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['user_id', 'name', 'location', 'orders_count', 'spent', 'contact', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Build normalized exact phone candidate strings (never substring LIKE) for Indonesian numbers.
     *
     * @param  string|int|null  $phone
     * @return array<int, string>
     */
    public static function phoneVariants($phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($digits) < 7) {
            return [];
        }

        $core = $digits;
        if (str_starts_with($core, '62')) {
            $core = substr($core, 2);
        } elseif (str_starts_with($core, '0')) {
            $core = substr($core, 1);
        }

        if ($core === '') {
            return [];
        }

        return array_values(array_unique([
            (string) $phone,
            $digits,
            $core,
            '0' . $core,
            '62' . $core,
            '+62' . $core,
        ]));
    }

    /**
     * Recalculate orders_count and spent for the Customer linked to a Pesanan.
     */
    public static function syncMetricsForOrder(?Pesanan $order): void
    {
        if (!$order) {
            return;
        }

        $user = null;
        if (!empty($order->user_id)) {
            $user = User::find($order->user_id);
        } elseif (!empty($order->email_pembeli)) {
            $user = User::where('email', $order->email_pembeli)->first();
        }

        if (!$user) {
            return;
        }

        $customer = self::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return;
        }

        $nonCanceledQuery = Pesanan::ownedBy($user)
            ->whereNotIn('order_status', ['batal', 'cancel', 'cancelled', 'failed']);

        $ordersCount = (clone $nonCanceledQuery)->count();
        $spent = (clone $nonCanceledQuery)
            ->where(function ($q) {
                $q->where('payment_status', 'Paid')
                  ->orWhereIn('order_status', ['selesai', 'completed', 'delivered']);
            })
            ->sum('total');

        $customer->update([
            'orders_count' => $ordersCount,
            'spent' => $spent,
        ]);
    }
}
