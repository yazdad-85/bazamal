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
        $type = $request->query('bazar', Product::TYPE_MENU);
        if ($type === 'besar') {
            $type = Product::TYPE_MENU;
        }
        if (! in_array($type, [Product::TYPE_MENU, Product::TYPE_INFAK], true)) {
            $type = Product::TYPE_MENU;
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
            'menuOrderingOpen' => Product::menuOrderingOpen(),
            'menuDeadlineLabel' => Product::menuDeadlineLabel(),
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
