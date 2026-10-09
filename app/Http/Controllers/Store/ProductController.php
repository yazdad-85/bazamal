<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, CartService $cart): View
    {
        $type = $request->query('bazar', 'besar');
        if (! in_array($type, ['besar', 'kecil'], true)) {
            $type = 'besar';
        }

        $products = Product::active()
            ->bazar($type)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(12);

        return view('store.products.index', [
            'products' => $products,
            'bazarType' => $type,
            'cartCount' => $cart->count(),
        ]);
    }

    public function show(Product $product, CartService $cart): View
    {
        abort_unless($product->is_active, 404);

        return view('store.products.show', [
            'product' => $product,
            'cartCount' => $cart->count(),
        ]);
    }
}
