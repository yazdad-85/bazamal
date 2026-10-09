<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderHistoryService;
use App\Support\Institutions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckoutForm extends Component
{
    use WithFileUploads;

    public string $buyer_type = 'umum';

    public string $institution = 'SMA';

    public string $class_name = '';

    public string $full_name = '';

    public string $phone = '';

    public string $pickup_method = 'ambil_stand';

    public string $delivery_address = '';

    public string $payment_method = 'online';

    public $payment_proof;

    public function updatedBuyerType(string $value): void
    {
        if ($value === 'umum') {
            $this->class_name = '';
        }
    }

    public function submit(CartService $cart)
    {
        if ($cart->isEmpty()) {
            $this->addError('cart', 'Keranjang kosong.');

            return;
        }

        $limitKey = 'checkout|'.session()->getId().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($limitKey, 10)) {
            $this->addError('cart', 'Terlalu banyak pesanan dari perangkat ini. Coba lagi dalam beberapa menit.');

            return;
        }
        RateLimiter::hit($limitKey, 600);

        $rules = [
            'buyer_type' => ['required', Rule::in(['umum', 'siswa'])],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => $this->buyer_type === 'siswa'
                ? ['nullable', 'string', 'max:30']
                : ['required', 'string', 'max:30'],
            'pickup_method' => ['required', Rule::in(['ambil_stand', 'kirim_alamat'])],
            'payment_method' => ['required', Rule::in(['online', 'transfer', 'tunai'])],
        ];

        if ($this->buyer_type === 'siswa') {
            $rules['institution'] = ['required', Rule::in(Institutions::options())];
            $rules['class_name'] = ['required', 'string', 'max:50'];
        }

        if ($this->pickup_method === 'kirim_alamat' && ! $cart->allowsHomeDelivery()) {
            $this->addError('pickup_method', 'Kirim ke alamat hanya untuk pesanan yang seluruhnya dari Bazar Besar.');

            return;
        }

        if ($this->pickup_method === 'kirim_alamat') {
            $rules['delivery_address'] = ['required', 'string', 'max:500'];
        }

        if ($this->payment_method === 'transfer') {
            $rules['payment_proof'] = ['required', 'image', 'max:2048'];
        }

        $this->validate($rules);

        try {
            $order = DB::transaction(function () use ($cart) {
                $items = $cart->items();
                $subtotal = 0;

                foreach ($items as $item) {
                    $product = Product::whereKey($item['product_id'])->lockForUpdate()->first();

                    if (! $product || ! $product->is_active || $product->stock < $item['qty']) {
                        throw new \RuntimeException('Stok tidak mencukupi untuk: '.($product->name ?? $item['name']));
                    }

                    $subtotal += $product->price * $item['qty'];
                }

                $status = $this->payment_method === 'tunai'
                    ? 'diproses'
                    : 'menunggu_verifikasi';

                $proofPath = null;
                if ($this->payment_method === 'transfer' && $this->payment_proof) {
                    $proofPath = $this->payment_proof->store('payment-proofs', 'local');
                }

                $order = Order::create([
                    'order_code' => Order::generateCode(),
                    'buyer_type' => $this->buyer_type,
                    'institution' => $this->buyer_type === 'siswa' ? $this->institution : null,
                    'class_name' => $this->buyer_type === 'siswa' ? $this->class_name : null,
                    'full_name' => $this->full_name,
                    'phone' => $this->phone ?: null,
                    'pickup_method' => $this->pickup_method,
                    'delivery_address' => $this->pickup_method === 'kirim_alamat' ? $this->delivery_address : null,
                    'payment_method' => $this->payment_method,
                    'payment_proof' => $proofPath,
                    'subtotal' => $subtotal,
                    'status' => $status,
                ]);

                foreach ($items as $item) {
                    $product = Product::whereKey($item['product_id'])->lockForUpdate()->first();
                    $lineTotal = $product->price * $item['qty'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'qty' => $item['qty'],
                        'line_total' => $lineTotal,
                    ]);

                    $product->decrement('stock', $item['qty']);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            $this->addError('cart', $e->getMessage());

            return;
        }

        $cart->clear();
        app(OrderHistoryService::class)->remember($order);

        return redirect()->route('checkout.success', ['orderCode' => $order->order_code]);
    }

    public function render(CartService $cart)
    {
        return view('livewire.checkout-form', [
            'institutions' => Institutions::options(),
            'bankInfo' => Setting::bankInfo(),
            'canDeliver' => $cart->allowsHomeDelivery(),
            'qrisUrl' => Setting::qrisUrl(),
        ]);
    }
}
