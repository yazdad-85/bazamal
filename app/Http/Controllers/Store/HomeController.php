<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CartService $cart): View
    {
        $products = Product::active()->latest()->take(8)->get();

        return view('store.home', [
            'products' => $products,
            'cartCount' => $cart->count(),
            'donationTotal' => Setting::donationTotal(),
        ]);
    }
}
