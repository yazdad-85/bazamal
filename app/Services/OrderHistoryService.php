<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class OrderHistoryService
{
    private const KEY = 'my_orders';

    public function remember(Order $order): void
    {
        $codes = collect($this->codes())
            ->prepend($order->order_code)
            ->unique()
            ->take(20)
            ->values()
            ->all();

        Session::put(self::KEY, $codes);
        Session::put('order_access.'.$order->order_code, true);
    }

    public function grantAccess(string $orderCode): void
    {
        Session::put('order_access.'.$orderCode, true);

        $codes = collect($this->codes())
            ->prepend($orderCode)
            ->unique()
            ->take(20)
            ->values()
            ->all();

        Session::put(self::KEY, $codes);
    }

    public function canAccess(string $orderCode): bool
    {
        return (bool) Session::get('order_access.'.$orderCode, false);
    }

    public function codes(): array
    {
        return Session::get(self::KEY, []);
    }

    public function orders(): Collection
    {
        $codes = $this->codes();

        if ($codes === []) {
            return collect();
        }

        return Order::query()
            ->with('items')
            ->whereIn('order_code', $codes)
            ->latest()
            ->get();
    }
}
