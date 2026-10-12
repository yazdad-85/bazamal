<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(CartService $cart): View
    {
        return view('store.cart.index', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'cartCount' => $cart->count(),
        ]);
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);

        if ($product->orderingClosed()) {
            return back()->with('error', 'Pemesanan Menu Bazar ditutup pada '.Product::menuDeadlineLabel().'. Infak & Sedekah tetap bisa dipesan.');
        }

        if ($product->stock < 1) {
            return back()->with('error', 'Stok produk ini habis.');
        }

        $cart->add($product, $data['qty'] ?? 1);
        $request->session()->forget('cart_confirmed');

        return redirect()
            ->route('cart.confirm')
            ->with('success', $product->name.' masuk ke pesanan. Periksa dulu sebelum membayar.');
    }

    public function confirm(CartService $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang masih kosong.');
        }

        return view('store.cart.confirm', [
            'items' => $cart->items(),
            'subtotal' => $cart->subtotal(),
            'cartCount' => $cart->count(),
        ]);
    }

    public function proceed(Request $request, CartService $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('home')->with('error', 'Keranjang masih kosong.');
        }

        $request->session()->put('cart_confirmed', true);

        return redirect()->route('checkout.show');
    }

    public function update(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart->update((int) $data['product_id'], (int) $data['qty']);
        $request->session()->forget('cart_confirmed');

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart->remove((int) $data['product_id']);
        $request->session()->forget('cart_confirmed');

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
