<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderHistoryService;
use App\Services\WhatsAppLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(CartService $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang masih kosong.');
        }

        return view('store.checkout.show', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'cartCount' => $cart->count(),
            'bankInfo' => Setting::bankInfo(),
        ]);
    }

    public function success(
        string $orderCode,
        WhatsAppLinkService $whatsApp,
        OrderHistoryService $history,
    ): View|RedirectResponse {
        $order = $this->orderForSession($orderCode, $history);

        if (! $order) {
            return $this->denyOrder();
        }

        $order->load('items');

        return view('store.checkout.success', [
            'order' => $order,
            'whatsappLinks' => $whatsApp->linksForOrder($order),
            'qrisUrl' => $order->payment_method === 'online' ? Setting::qrisUrl() : null,
            'cartCount' => 0,
        ]);
    }

    private function orderForSession(string $orderCode, OrderHistoryService $history): ?Order
    {
        $order = Order::query()->where('order_code', $orderCode)->first();

        if (! $order || ! $history->canAccess($order->order_code)) {
            return null;
        }

        return $order;
    }

    private function denyOrder(): RedirectResponse
    {
        return redirect()
            ->route('orders.index')
            ->with('error', 'Pesanan hanya bisa dibuka di perangkat yang memesan, atau dengan kode pesanan dan nama lengkap.');
    }
}
