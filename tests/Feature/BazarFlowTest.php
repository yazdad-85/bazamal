<?php

namespace Tests\Feature;

use App\Livewire\CheckoutForm;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BazarFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_catalog_render(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Menu Bazar')
            ->assertSee('Infak & Sedekah')
            ->assertDontSee('Bazar Kecil')
            ->assertDontSee('2 Tombol Menu Utama')
            ->assertDontSee('Toko amal untuk siswa')
            ->assertSee('Copyright '.now()->year)
            ->assertDontSee('Login Panitia')
            ->assertDontSee('>Admin<', false)
            ->assertHeader('X-Frame-Options', 'DENY');
        $this->get('/bazar?bazar=infak')->assertOk()->assertSee('Infak & Sedekah');
    }

    public function test_checkout_siswa_creates_order_and_reduces_stock(): void
    {
        $this->seed();

        $product = Product::where('slug', 'tote-bag-kanvas-amal')->firstOrFail();
        $stockBefore = $product->stock;

        $cart = app(CartService::class);
        $cart->add($product, 1);

        Livewire::test(CheckoutForm::class)
            ->set('buyer_type', 'siswa')
            ->assertDontSee('Diantar ke Kelas')
            ->set('institution', 'SMA')
            ->set('class_name', 'XI-IPA 2')
            ->set('full_name', 'Andi Siswa')
            ->set('phone', '')
            ->set('pickup_method', 'ambil_stand')
            ->set('payment_method', 'tunai')
            ->call('submit')
            ->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('siswa', $order->buyer_type);
        $this->assertSame('ambil_stand', $order->pickup_method);
        $this->assertNull($order->phone);
        $this->assertSame('SMA', $order->institution);
        $this->assertSame('diproses', $order->status);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame($stockBefore - 1, $product->fresh()->stock);

        $this->get(route('checkout.success', ['orderCode' => $order->order_code]))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertSee('Pesanan Tersimpan');

        $this->get(route('orders.show', ['orderCode' => $order->order_code]))
            ->assertOk()
            ->assertSee('Barang yang dipesan')
            ->assertSee($order->status_label);
    }

    public function test_buyer_can_lookup_saved_order_by_code_and_name(): void
    {
        $this->seed();

        $product = Product::firstOrFail();
        $cart = app(CartService::class);
        $cart->add($product, 1);

        Livewire::test(CheckoutForm::class)
            ->set('buyer_type', 'umum')
            ->set('full_name', 'Budi Umum')
            ->set('phone', '081111111111')
            ->set('pickup_method', 'ambil_stand')
            ->set('payment_method', 'online')
            ->call('submit')
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertGreaterThan(0, $order->items()->count());

        $this->post(route('orders.lookup'), [
            'order_code' => $order->order_code,
            'full_name' => 'Budi Umum',
        ])->assertRedirect(route('orders.show', ['orderCode' => $order->order_code]));

        $this->get(route('orders.show', ['orderCode' => $order->order_code]))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Menunggu Verifikasi');
    }

    public function test_home_delivery_is_only_for_bazar_besar_and_has_no_shipping_fee(): void
    {
        $this->seed();

        $besar = Product::where('slug', 'kaos-kebaikan')->firstOrFail();
        $infak = Product::where('slug', 'infak-sedekah')->firstOrFail();
        $price = $besar->price;

        $cart = app(CartService::class);
        $cart->add($besar, 1);

        Livewire::test(CheckoutForm::class)
            ->assertSee('Kirim ke Alamat')
            ->assertSee('Tanpa ongkir, diantar tim panitia')
            ->set('buyer_type', 'umum')
            ->set('full_name', 'Siti Umum')
            ->set('phone', '081222222222')
            ->set('pickup_method', 'kirim_alamat')
            ->set('delivery_address', 'Jl. Melati No. 8, RT 02/RW 03, Kelurahan Sumber')
            ->set('payment_method', 'tunai')
            ->call('submit')
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame('kirim_alamat', $order->pickup_method);
        $this->assertSame('Jl. Melati No. 8, RT 02/RW 03, Kelurahan Sumber', $order->delivery_address);
        $this->assertSame($price, $order->subtotal);

        $this->get(route('checkout.success', ['orderCode' => $order->order_code]))
            ->assertOk()
            ->assertSee('Kirim ke Alamat')
            ->assertSee('Jl. Melati No. 8');

        $cart->add($infak, 1);

        Livewire::test(CheckoutForm::class)
            ->assertSee('Hanya jika semua barang di keranjang dari Menu Bazar')
            ->set('buyer_type', 'umum')
            ->set('full_name', 'Siti Umum')
            ->set('phone', '081222222222')
            ->set('pickup_method', 'kirim_alamat')
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('payment_method', 'tunai')
            ->call('submit')
            ->assertHasErrors('pickup_method');

        $this->assertSame(1, Order::count());
    }

    public function test_order_details_are_not_visible_from_another_session(): void
    {
        $this->seed();

        $product = Product::where('slug', 'kaos-kebaikan')->firstOrFail();
        app(CartService::class)->add($product, 1);

        Livewire::test(CheckoutForm::class)
            ->set('buyer_type', 'umum')
            ->set('full_name', 'Rahasia Pembeli')
            ->set('phone', '081234567890')
            ->set('pickup_method', 'ambil_stand')
            ->set('payment_method', 'tunai')
            ->call('submit')
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame(26, strlen($order->order_code));

        $this->flushSession();

        $this->get(route('checkout.success', ['orderCode' => $order->order_code]))
            ->assertRedirect(route('orders.index'))
            ->assertDontSee('Rahasia Pembeli');

        $this->get(route('orders.show', ['orderCode' => $order->order_code]))
            ->assertRedirect(route('orders.index'))
            ->assertDontSee('Rahasia Pembeli');

        $this->get(route('checkout.success', ['orderCode' => 'BA-000101-TIDAKADA']))
            ->assertRedirect(route('orders.index'));
    }

    public function test_transfer_proof_is_not_public(): void
    {
        $this->seed();
        Storage::fake('local');
        Storage::fake('public');

        $product = Product::where('slug', 'kaos-kebaikan')->firstOrFail();
        app(CartService::class)->add($product, 1);

        Livewire::test(CheckoutForm::class)
            ->set('buyer_type', 'umum')
            ->set('full_name', 'Pembeli Transfer')
            ->set('phone', '081234567890')
            ->set('pickup_method', 'ambil_stand')
            ->set('payment_method', 'transfer')
            ->set('payment_proof', UploadedFile::fake()->image('bukti.jpg'))
            ->call('submit')
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        Storage::disk('local')->assertExists($order->payment_proof);
        Storage::disk('public')->assertMissing($order->payment_proof);

        $this->get(route('admin.orders.proof', $order))->assertRedirect(route('login'));

        $admin = User::where('email', 'admin@bazar.test')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.orders.proof', $order))
            ->assertOk();
    }

    public function test_menu_orders_close_on_1_december_while_infak_stays_open(): void
    {
        $this->seed();

        $menu = Product::where('slug', 'kaos-kebaikan')->firstOrFail();
        $infak = Product::where('slug', 'infak-sedekah')->firstOrFail();

        $this->travelTo(Carbon::parse('2026-11-30 21:00:00', 'Asia/Jakarta'));
        $this->post(route('cart.store'), ['product_id' => $menu->id, 'qty' => 1])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->travelTo(Carbon::parse('2026-12-01 00:05:00', 'Asia/Jakarta'));
        $this->post(route('cart.store'), ['product_id' => $menu->id, 'qty' => 1])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->post(route('cart.store'), ['product_id' => $infak->id, 'qty' => 1])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_admin_can_access_dashboard_and_export(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@bazar.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Menu Bazar')
            ->assertSee('Infak & Sedekah', false);

        $this->actingAs($admin)
            ->get('/admin/reports/export')
            ->assertOk();
    }

    public function test_admin_can_update_settings(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@bazar.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Bendahara Inti')
            ->assertSee('Gambar QRIS');

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'site_name' => 'Bazar Amal Yayasan',
                'bank_info' => 'BSI 111 a.n. Bendahara',
                'wa_bendahara' => '6281111111111',
                'wa_mts' => '6282222222222',
                'wa_smp' => '',
                'wa_ma' => '',
                'wa_sma' => '6283333333333',
                'wa_smk' => '',
            ])
            ->assertRedirect();

        $this->assertSame('Bazar Amal Yayasan', \App\Models\Setting::siteName());
        $this->assertSame('6283333333333', \App\Models\Setting::institutionWhatsApp('SMA'));
        $this->assertSame('6281111111111', \App\Models\Setting::bendaharaWhatsApp());
    }
}
