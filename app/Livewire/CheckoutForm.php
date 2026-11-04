<?php

namespace App\Livewire;

use App\Models\DeliveryVillage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\DeliveryFeeCalculator;
use App\Services\OrderHistoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckoutForm extends Component
{
    use WithFileUploads;

    public const OUTSIDE_AREA = 'luar';

    public const BAWEAN_AREA = 'bawean';

    public const OUTSIDE_AREA_MESSAGE = 'Anda berada di luar Kabupaten Gresik. Menu Bazar hanya tersedia untuk wilayah Kabupaten Gresik, silakan pilih menu Infak.';

    public const BAWEAN_AREA_MESSAGE = 'Pengantaran Menu Bazar belum menjangkau Pulau Bawean, silakan pilih menu Infak.';

    public string $full_name = '';

    public string $phone = '';

    public string $delivery_district = '';

    public string $delivery_village_id = '';

    public string $district_search = '';

    public string $village_search = '';

    public string $delivery_address = '';

    public string $delivery_date = '';

    public string $payment_method = 'online';

    public $payment_proof;

    public function updatedDeliveryDistrict(): void
    {
        $this->delivery_village_id = '';
        $this->district_search = '';
        $this->village_search = '';
        $this->resetValidation('delivery_village_id');
    }

    public function selectVillage(int $villageId): void
    {
        $village = DeliveryVillage::query()->find($villageId);

        if (! $village) {
            return;
        }

        $this->delivery_district = $village->district;
        $this->updatedDeliveryDistrict();
        $this->delivery_village_id = (string) $village->id;
    }

    public function changeDistrict(): void
    {
        $this->delivery_district = '';
        $this->updatedDeliveryDistrict();
    }

    public function changeVillage(): void
    {
        $this->delivery_village_id = '';
        $this->village_search = '';
    }

    public function submit(CartService $cart)
    {
        if ($cart->isEmpty()) {
            $this->addError('cart', 'Keranjang kosong.');

            return;
        }

        if ($cart->items()->contains(function ($item) {
            $product = Product::find($item['product_id']);

            return $product?->orderingClosed() ?? true;
        })) {
            $this->addError('cart', 'Pemesanan Menu Bazar ditutup pada '.Product::menuDeadlineLabel().'. Keluarkan menu tersebut dari keranjang. Infak & Sedekah tetap bisa dipesan.');

            return;
        }

        $hasMenuItems = $cart->containsMenuItems();
        $deliveryVillage = null;
        $deliveryFee = 0;

        if ($hasMenuItems) {
            if ($blockedAreaMessage = $this->blockedAreaMessage()) {
                $this->addError('cart', $blockedAreaMessage);

                return;
            }

            $deliveryVillage = $this->selectedVillage();

            if (! $deliveryVillage) {
                $this->addError('delivery_village_id', 'Pilih kecamatan dan desa tujuan pengiriman.');

                return;
            }

            $deliveryFee = app(DeliveryFeeCalculator::class)->feeFor($deliveryVillage);

            if ($deliveryFee === null) {
                $this->addError('delivery_village_id', 'Ongkir untuk desa ini belum diatur panitia. Silakan hubungi panitia.');

                return;
            }
        }

        $limitKey = 'checkout|'.session()->getId().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($limitKey, 10)) {
            $this->addError('cart', 'Terlalu banyak pesanan dari perangkat ini. Coba lagi dalam beberapa menit.');

            return;
        }
        RateLimiter::hit($limitKey, 600);

        $deliveryDates = config('bazar.menu_delivery_dates', []);

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'delivery_address' => $hasMenuItems ? ['required', 'string', 'max:500'] : ['nullable'],
            'delivery_date' => $hasMenuItems
                ? ['required', 'date', Rule::in(array_keys($deliveryDates))]
                : ['nullable'],
            'payment_method' => ['required', Rule::in(array_keys(Order::checkoutPaymentMethods()))],
        ];

        if (in_array($this->payment_method, ['online', 'transfer'], true)) {
            $rules['payment_proof'] = ['required', 'image', 'max:2048'];
        }

        $this->validate($rules, [
            'delivery_date.required' => 'Pilih hari pengiriman untuk Menu Bazar.',
            'delivery_date.date' => 'Hari pengiriman tidak valid.',
            'delivery_date.in' => 'Pilih salah satu hari pengiriman yang tersedia.',
            'payment_proof.required' => 'Unggah bukti pembayaran terlebih dahulu.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar JPG atau PNG.',
            'payment_proof.max' => 'Ukuran bukti pembayaran terlalu besar. Pilih gambar lain atau tangkapan layar.',
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $hasMenuItems, $deliveryVillage, $deliveryFee) {
                $items = $cart->items();
                $subtotal = 0;

                foreach ($items as $item) {
                    $product = Product::whereKey($item['product_id'])->lockForUpdate()->first();

                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException('Produk tidak tersedia: '.($item['name'] ?? 'item'));
                    }

                    if ($product->orderingClosed()) {
                        throw new \RuntimeException('Pemesanan Menu Bazar sudah ditutup. Infak & Sedekah tetap bisa dipesan.');
                    }

                    if ($product->stock < $item['qty']) {
                        throw new \RuntimeException('Stok tidak mencukupi untuk: '.$product->name);
                    }

                    $subtotal += $product->price * $item['qty'];
                }

                $status = $this->payment_method === 'tunai'
                    ? 'diproses'
                    : 'menunggu_verifikasi';

                $proofPath = null;
                if (in_array($this->payment_method, ['online', 'transfer'], true) && $this->payment_proof) {
                    $proofPath = $this->payment_proof->store('payment-proofs', 'local');
                }

                $order = Order::create([
                    'order_code' => Order::generateCode(),
                    'buyer_type' => 'umum',
                    'institution' => null,
                    'class_name' => null,
                    'full_name' => $this->full_name,
                    'phone' => $this->phone,
                    'pickup_method' => $hasMenuItems ? 'kirim_alamat' : 'tanpa_pengiriman',
                    'delivery_address' => $hasMenuItems ? $this->delivery_address : null,
                    'delivery_village_id' => $deliveryVillage?->getKey(),
                    'delivery_area' => $deliveryVillage?->area_label,
                    'delivery_distance_km' => $deliveryVillage?->distance_km,
                    'delivery_date' => $hasMenuItems ? $this->delivery_date : null,
                    'payment_method' => $this->payment_method,
                    'payment_proof' => $proofPath,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
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

    private function blockedAreaMessage(): ?string
    {
        return match ($this->delivery_district) {
            self::OUTSIDE_AREA => self::OUTSIDE_AREA_MESSAGE,
            self::BAWEAN_AREA => self::BAWEAN_AREA_MESSAGE,
            default => null,
        };
    }

    private function selectedVillage(): ?DeliveryVillage
    {
        if (! ctype_digit($this->delivery_village_id)) {
            return null;
        }

        return DeliveryVillage::query()
            ->where('district', $this->delivery_district)
            ->find((int) $this->delivery_village_id);
    }

    private function matchesSearch(string $text, string $search): bool
    {
        return trim($search) === '' || Str::contains($text, trim($search), ignoreCase: true);
    }

    /**
     * @return Collection<int, DeliveryVillage>
     */
    private function villagesMatchingDistrictSearch(): Collection
    {
        if (Str::length(trim($this->district_search)) < 2) {
            return collect();
        }

        return DeliveryVillage::query()
            ->orderBy('name')
            ->get()
            ->filter(fn (DeliveryVillage $village): bool => $this->matchesSearch($village->name, $this->district_search))
            ->take(10)
            ->values();
    }

    public function render()
    {
        $cart = app(CartService::class);
        $hasMenuItems = $cart->containsMenuItems();
        $selectedVillage = $hasMenuItems ? $this->selectedVillage() : null;
        $deliveryFee = $selectedVillage ? app(DeliveryFeeCalculator::class)->feeFor($selectedVillage) : null;
        $subtotal = $cart->subtotal();
        $districts = DeliveryVillage::query()->distinct()->orderBy('district')->pluck('district');

        return view('livewire.checkout-form', [
            'districtResults' => $districts
                ->filter(fn (string $district): bool => $this->matchesSearch($district, $this->district_search))
                ->values(),
            'showBaweanOption' => $this->matchesSearch('Pulau Bawean Sangkapura Tambak', $this->district_search),
            'villageMatches' => $this->delivery_district === '' ? $this->villagesMatchingDistrictSearch() : collect(),
            'freeDeliveryDistricts' => DeliveryVillage::FREE_DELIVERY_DISTRICTS,
            'villageResults' => $districts->contains($this->delivery_district)
                ? DeliveryVillage::query()
                    ->where('district', $this->delivery_district)
                    ->orderBy('name')
                    ->get()
                    ->filter(fn (DeliveryVillage $village): bool => $this->matchesSearch($village->name, $this->village_search))
                    ->values()
                : collect(),
            'isKnownDistrict' => $districts->contains($this->delivery_district),
            'selectedDistrictLabel' => match ($this->delivery_district) {
                self::BAWEAN_AREA => 'Pulau Bawean (Sangkapura, Tambak)',
                self::OUTSIDE_AREA => 'Di luar Kabupaten Gresik',
                default => $this->delivery_district,
            },
            'blockedAreaMessage' => $hasMenuItems ? $this->blockedAreaMessage() : null,
            'selectedVillage' => $selectedVillage,
            'deliveryFee' => $deliveryFee,
            'deliveryFeeLabel' => match ($deliveryFee) {
                null => '-',
                0 => 'Gratis',
                default => 'Rp '.number_format($deliveryFee, 0, ',', '.'),
            },
            'canContinue' => ! $hasMenuItems || $deliveryFee !== null,
            'subtotal' => $subtotal,
            'total' => $subtotal + ($deliveryFee ?? 0),
            'bankInfo' => Setting::bankInfo(),
            'deliveryDates' => config('bazar.menu_delivery_dates', []),
            'hasMenuItems' => $hasMenuItems,
            'qrisUrl' => Setting::qrisUrl(),
            'paymentMethods' => Order::checkoutPaymentMethods(),
        ]);
    }
}
