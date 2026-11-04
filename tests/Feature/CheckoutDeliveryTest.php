<?php

namespace Tests\Feature;

use App\Livewire\CheckoutForm;
use App\Models\DeliveryVillage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\DeliveryFeeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CheckoutDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_migration_provides_every_manyar_and_bungah_village(): void
    {
        $this->assertSame(23, DeliveryVillage::query()->where('district', 'Manyar')->count());
        $this->assertSame(22, DeliveryVillage::query()->where('district', 'Bungah')->count());
    }

    #[TestWith(['5', 2000, 5000, 1000, 10000])]
    #[TestWith(['1', 2000, 5000, 1000, 5000])]
    #[TestWith(['3.21', 1500, 0, 1000, 5000])]
    #[TestWith(['3.21', 1500, 0, 0, 4815])]
    #[TestWith(['2', 1000, 0, 500, 2000])]
    public function test_fee_applies_rate_rounding_and_minimum(string $distance, int $rate, int $minimum, int $rounding, int $expectedFee): void
    {
        $village = DeliveryVillage::factory()->make(['distance_km' => $distance]);
        $this->configureFees($rate, $minimum, $rounding);

        $this->assertSame($expectedFee, app(DeliveryFeeCalculator::class)->feeFor($village));
    }

    public function test_fee_is_unavailable_without_a_rate_or_distance(): void
    {
        $calculator = app(DeliveryFeeCalculator::class);

        $this->assertNull($calculator->feeFor(DeliveryVillage::factory()->make(['distance_km' => 5])));

        $this->configureFees(2000, 5000, 1000);

        $this->assertNull($calculator->feeFor(DeliveryVillage::factory()->make(['distance_km' => null])));
    }

    public function test_menu_checkout_stores_the_area_distance_fee_and_total(): void
    {
        $village = $this->configuredVillage(distance: 5);
        $product = $this->addToCart(Product::TYPE_MENU);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_district', 'Manyar')
            ->set('delivery_village_id', (string) $village->id)
            ->assertSee('Ongkir ke '.$village->name)
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('delivery_date', '2026-12-16')
            ->call('submit')
            ->assertRedirect();

        $order = Order::query()->sole();

        $this->assertSame('kirim_alamat', $order->pickup_method);
        $this->assertSame($village->id, $order->delivery_village_id);
        $this->assertSame($village->name.', Kec. Manyar', $order->delivery_area);
        $this->assertSame('5.00', $order->delivery_distance_km);
        $this->assertSame(10000, $order->delivery_fee);
        $this->assertSame($product->price, $order->subtotal);
        $this->assertSame($product->price + 10000, $order->total);
    }

    public function test_later_rate_changes_do_not_alter_a_placed_order(): void
    {
        $village = $this->configuredVillage(distance: 5);
        $this->addToCart(Product::TYPE_MENU);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_district', 'Manyar')
            ->set('delivery_village_id', (string) $village->id)
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('delivery_date', '2026-12-16')
            ->call('submit');

        $this->configureFees(9000, 0, 0);
        $village->update(['distance_km' => 50]);

        $order = Order::query()->sole();

        $this->assertSame(10000, $order->delivery_fee);
        $this->assertSame('5.00', $order->delivery_distance_km);
    }

    public function test_outside_area_buyer_with_menu_items_is_blocked_with_the_notice(): void
    {
        $this->configuredVillage(distance: 5);
        $this->addToCart(Product::TYPE_MENU);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_district', CheckoutForm::OUTSIDE_AREA)
            ->assertSee(CheckoutForm::OUTSIDE_AREA_MESSAGE)
            ->assertSeeHtml('<fieldset disabled')
            ->assertSeeHtml(route('products.index', ['bazar' => 'infak']))
            ->set('delivery_address', 'Jl. Jauh No. 1')
            ->set('delivery_date', '2026-12-16')
            ->call('submit')
            ->assertHasErrors('cart')
            ->assertNoRedirect();

        $this->assertSame(0, Order::query()->count());
    }

    public function test_menu_checkout_requires_a_village_in_the_chosen_district(): void
    {
        $bungahVillage = DeliveryVillage::query()->where('district', 'Bungah')->firstOrFail();
        $this->configuredVillage(distance: 5);
        $bungahVillage->update(['distance_km' => 5]);
        $this->addToCart(Product::TYPE_MENU);

        $checkout = $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('delivery_date', '2026-12-16');

        $checkout->assertSeeHtml('<fieldset disabled')
            ->call('submit')
            ->assertHasErrors('delivery_village_id');

        $checkout->set('delivery_district', 'Manyar')
            ->set('delivery_village_id', (string) $bungahVillage->id)
            ->call('submit')
            ->assertHasErrors('delivery_village_id')
            ->assertSee('Pilih kecamatan dan desa tujuan pengiriman.');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_menu_checkout_is_blocked_until_the_fee_is_configured(): void
    {
        $village = DeliveryVillage::query()->where('district', 'Manyar')->firstOrFail();
        $this->addToCart(Product::TYPE_MENU);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_district', 'Manyar')
            ->set('delivery_village_id', (string) $village->id)
            ->assertSee('Ongkir untuk desa ini belum diatur panitia.')
            ->assertSeeHtml('<fieldset disabled')
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('delivery_date', '2026-12-16')
            ->call('submit')
            ->assertHasErrors('delivery_village_id');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_infak_only_checkout_needs_no_location_address_or_fee(): void
    {
        $product = $this->addToCart(Product::TYPE_INFAK);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->assertDontSee('Lokasi Pengiriman')
            ->assertDontSee('Alamat pengiriman')
            ->assertDontSeeHtml('<fieldset disabled')
            ->call('submit')
            ->assertRedirect();

        $order = Order::query()->sole();

        $this->assertSame('tanpa_pengiriman', $order->pickup_method);
        $this->assertNull($order->delivery_address);
        $this->assertNull($order->delivery_area);
        $this->assertSame(0, $order->delivery_fee);
        $this->assertSame($product->price, $order->total);
    }

    public function test_outside_area_buyer_can_still_check_out_with_only_infak(): void
    {
        $this->addToCart(Product::TYPE_INFAK);

        $this->fillBuyer(Livewire::test(CheckoutForm::class))
            ->set('delivery_district', CheckoutForm::OUTSIDE_AREA)
            ->assertDontSee(CheckoutForm::OUTSIDE_AREA_MESSAGE)
            ->call('submit')
            ->assertRedirect();

        $this->assertSame(1, Order::query()->count());
    }

    private function fillBuyer(mixed $checkout): mixed
    {
        return $checkout
            ->set('full_name', 'Pembeli Ongkir')
            ->set('phone', '081234567890')
            ->set('payment_method', 'transfer')
            ->set('payment_proof', UploadedFile::fake()->image('bukti.jpg'));
    }

    private function addToCart(string $bazarType): Product
    {
        $product = Product::query()->where('bazar_type', $bazarType)->firstOrFail();
        app(CartService::class)->add($product, 1);

        return $product;
    }

    private function configuredVillage(int $distance): DeliveryVillage
    {
        $this->configureFees(2000, 5000, 1000);

        $village = DeliveryVillage::query()->where('district', 'Manyar')->firstOrFail();
        $village->update(['distance_km' => $distance]);

        return $village;
    }

    private function configureFees(int $rate, int $minimum, int $rounding): void
    {
        Setting::setMany([
            'delivery_rate_per_km' => (string) $rate,
            'delivery_min_fee' => (string) $minimum,
            'delivery_fee_rounding' => (string) $rounding,
        ]);
    }
}
