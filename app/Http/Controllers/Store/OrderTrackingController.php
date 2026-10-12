<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderHistoryService;
use App\Services\WhatsAppLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(CartService $cart, OrderHistoryService $history): View
    {
        return view('store.orders.index', [
            'orders' => $history->orders(),
            'cartCount' => $cart->count(),
        ]);
    }

    public function lookup(Request $request, OrderHistoryService $history): RedirectResponse
    {
        $data = $request->validate([
            'order_code' => ['required', 'string', 'max:40'],
            'full_name' => ['required', 'string', 'max:255'],
        ]);

        $code = strtoupper(trim($data['order_code']));
        $name = mb_strtolower(trim($data['full_name']));

        $order = Order::query()
            ->whereRaw('UPPER(order_code) = ?', [$code])
            ->get()
            ->first(fn (Order $o) => mb_strtolower(trim($o->full_name)) === $name);

        if (! $order) {
            return back()
                ->withInput()
                ->with('error', 'Pesanan tidak ditemukan. Periksa kode pesanan dan nama lengkap.');
        }

        $history->grantAccess($order->order_code);

        return redirect()->route('orders.show', ['orderCode' => $order->order_code]);
    }

    public function show(
        string $orderCode,
        CartService $cart,
        OrderHistoryService $history,
        WhatsAppLinkService $whatsApp,
    ): View|RedirectResponse {
        $order = Order::query()->where('order_code', $orderCode)->first();

        if (! $order || ! $history->canAccess($order->order_code)) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Pesanan hanya bisa dibuka di perangkat yang memesan, atau dengan kode pesanan dan nama lengkap.');
        }

        $order->load('items');

        return view('store.orders.show', [
            'order' => $order,
            'whatsappLinks' => $whatsApp->linksForOrder($order),
            'qrisUrl' => $order->payment_method === 'online' ? Setting::qrisUrl() : null,
            'cartCount' => $cart->count(),
            'statusSteps' => [
                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                'diproses' => 'Diproses',
                'siap_diantar' => 'Siap Diantar',
                'selesai' => 'Selesai',
            ],
        ]);
    }

    public function cancel(string $orderCode, OrderHistoryService $history): RedirectResponse
    {
        $order = Order::query()->where('order_code', $orderCode)->first();

        if (! $order || ! $history->canAccess($order->order_code)) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Pesanan hanya bisa dibuka di perangkat yang memesan, atau dengan kode pesanan dan nama lengkap.');
        }

        $cancelled = DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->buyerCanChangeMenu()) {
                return false;
            }

            foreach ($locked->items()->get() as $item) {
                if (! $item->product_id) {
                    continue;
                }

                Product::query()->whereKey($item->product_id)->lockForUpdate()->increment('stock', $item->qty);
            }

            $locked->update(['status' => 'batal']);

            return true;
        });

        if (! $cancelled) {
            return redirect()
                ->route('orders.show', ['orderCode' => $order->order_code])
                ->with('error', 'Pesanan sudah diproses panitia, jadi menu tidak bisa diganti dari halaman ini.');
        }

        return redirect()
            ->route('products.index', ['bazar' => 'menu'])
            ->with('success', 'Pesanan dibatalkan dan stok dikembalikan. Silakan pilih menu yang benar.');
    }
}
