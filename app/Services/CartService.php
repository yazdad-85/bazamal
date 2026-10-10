<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const KEY = 'cart';

    public function all(): array
    {
        return Session::get(self::KEY, []);
    }

    public function count(): int
    {
        return collect($this->all())->sum('qty');
    }

    public function add(Product $product, int $qty = 1): void
    {
        $cart = $this->all();
        $id = (string) $product->id;
        $current = $cart[$id]['qty'] ?? 0;
        $newQty = min($current + $qty, max($product->stock, 0));

        if ($newQty < 1) {
            return;
        }

        $cart[$id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'qty' => $newQty,
            'bazar_type' => $product->bazar_type,
            'image' => $product->image,
        ];

        Session::put(self::KEY, $cart);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->all();
        $id = (string) $productId;

        if (! isset($cart[$id])) {
            return;
        }

        if ($qty < 1) {
            unset($cart[$id]);
        } else {
            $product = Product::find($productId);
            $max = $product?->stock ?? $cart[$id]['qty'];
            $cart[$id]['qty'] = min($qty, max($max, 0));
            if ($cart[$id]['qty'] < 1) {
                unset($cart[$id]);
            }
        }

        Session::put(self::KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->all();
        unset($cart[(string) $productId]);
        Session::put(self::KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    public function items(): Collection
    {
        return collect($this->all())->values();
    }

    public function subtotal(): int
    {
        return $this->items()->sum(fn ($item) => $item['price'] * $item['qty']);
    }

    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    public function allowsHomeDelivery(): bool
    {
        $items = $this->items();

        if ($items->isEmpty()) {
            return false;
        }

        return $items->every(fn ($item) => in_array($item['bazar_type'] ?? null, [Product::TYPE_MENU, 'besar'], true));
    }
}
