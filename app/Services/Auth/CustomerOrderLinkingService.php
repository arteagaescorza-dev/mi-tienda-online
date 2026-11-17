<?php

namespace App\Services\Auth;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerOrderLinkingService
{
    /**
     * Vincula órdenes previas de invitado (user_id IS NULL) cuyo correo coincida exactamente
     * con el correo ya verificado del comprador.
     */
    public function linkGuestOrdersForVerifiedUser(User $user): int
    {
        if (!$user->hasVerifiedEmail()) {
            return 0;
        }

        $email = strtolower(trim((string) $user->email));
        if ($email === '') {
            return 0;
        }

        return DB::transaction(function () use ($user, $email): int {
            $candidates = Order::query()
                ->whereNull('user_id')
                ->where(function ($q) use ($email) {
                    $q->whereRaw('LOWER(CAST(shipping_address AS TEXT)) LIKE ?', ['%' . $email . '%'])
                      ->orWhereRaw('LOWER(CAST(billing_address AS TEXT)) LIKE ?', ['%' . $email . '%']);
                })
                ->get();

            $linked = 0;
            foreach ($candidates as $order) {
                $shipEmail = strtolower(trim((string) data_get($order->shipping_address, 'email', '')));
                $billEmail = strtolower(trim((string) data_get($order->billing_address, 'email', '')));

                if ($shipEmail === $email || $billEmail === $email) {
                    $order->user_id = $user->id;
                    $order->save();
                    $linked++;
                }
            }

            return $linked;
        });
    }
}
