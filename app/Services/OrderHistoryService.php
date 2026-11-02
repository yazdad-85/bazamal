<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class OrderHistoryService
{
    private const KEY = 'my_orders';

    public function __construct(private DeviceIdentity $identity) {}

    public function remember(Order $order): void
    {
        $this->identity->attach($order);

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
        $order = Order::query()->where('order_code', $orderCode)->first();
        if ($order) {
            $this->identity->attach($order);
        }

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
        $order = Order::query()->where('order_code', $orderCode)->first();

        if (! $order) {
            return false;
        }

        if ($this->identity->canAccess($order)) {
            return true;
        }

        $hasLegacySessionAccess = (bool) Session::get('order_access.'.$orderCode, false);
        if ($hasLegacySessionAccess) {
            $this->identity->attach($order);
        }

        return $hasLegacySessionAccess;
    }

    public function codes(): array
    {
        return Session::get(self::KEY, []);
    }

    public function orders(): Collection
    {
        $device = $this->identity->current();

        if ($device) {
            $this->migrateSessionOrders();

            return $device->orders()
                ->with('items')
                ->orderByDesc('orders.created_at')
                ->orderByDesc('orders.id')
                ->limit(20)
                ->get();
        }

        $codes = $this->codes();

        return Order::query()
            ->with('items')
            ->whereIn('order_code', $codes)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    public function forgetDevice(): void
    {
        foreach ($this->codes() as $orderCode) {
            Session::forget('order_access.'.$orderCode);
        }

        Session::forget(self::KEY);
        $this->identity->forget();
    }

    private function migrateSessionOrders(): void
    {
        $orderIds = Order::query()
            ->whereIn('order_code', $this->codes())
            ->pluck('id');

        if ($orderIds->isNotEmpty()) {
            $this->identity->current()?->orders()->syncWithoutDetaching($orderIds);
        }
    }
}
