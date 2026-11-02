<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\PushNotificationService;

class OrderObserver
{
    public function __construct(private PushNotificationService $notifications) {}

    public function created(Order $order): void
    {
        $orderId = $order->getKey();

        defer(fn () => $this->notifications->notifyAdminsOfNewOrder($orderId));
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $orderId = $order->getKey();

        defer(fn () => $this->notifications->notifyCustomerOfStatusChange($orderId));
    }
}
