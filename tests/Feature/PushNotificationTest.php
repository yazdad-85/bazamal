<?php

namespace Tests\Feature;

use App\Contracts\PushSender;
use App\Livewire\CheckoutForm;
use App\Models\DeliveryVillage;
use App\Models\Device;
use App\Models\Order;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\DeviceIdentity;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_order_notification_is_sent_only_to_authorized_admin_devices(): void
    {
        $sender = $this->fakeSender();
        $inti = User::factory()->create(['role' => User::ROLE_INTI]);
        $coordinator = User::factory()->create([
            'role' => User::ROLE_KOORDINATOR,
            'institution' => 'SMP',
        ]);
        $this->createSubscription($this->createDevice('admin-1'), 'inti-token', $inti);
        $this->createSubscription($this->createDevice('admin-2'), 'coordinator-token', $coordinator);
        $order = $this->createOrder('BA-PUSH-ADMIN', buyerType: 'umum');

        $this->app->make(PushNotificationService::class)->notifyAdminsOfNewOrder($order->id);

        $this->assertCount(1, $sender->calls);
        $this->assertSame(['inti-token'], $sender->calls[0]['tokens']);
        $this->assertSame('Pesanan baru dari Pembeli Notifikasi', $sender->calls[0]['title']);
        $this->assertSame('Rp 25.000 · BA-PUSH-ADMIN', $sender->calls[0]['body']);
        $this->assertStringContainsString($order->order_code, $sender->calls[0]['body']);
    }

    public function test_status_notification_is_sent_only_to_devices_linked_to_the_order(): void
    {
        $sender = $this->fakeSender();
        $orderDevice = $this->createDevice('buyer-1');
        $otherDevice = $this->createDevice('buyer-2');
        $this->createSubscription($orderDevice, 'buyer-token');
        $this->createSubscription($otherDevice, 'other-token');
        $order = $this->createOrder('BA-PUSH-BUYER');
        $orderDevice->orders()->attach($order);

        $this->app->make(PushNotificationService::class)->notifyCustomerOfStatusChange($order->id);

        $this->assertCount(1, $sender->calls);
        $this->assertSame(['buyer-token'], $sender->calls[0]['tokens']);
    }

    public function test_invalid_tokens_returned_by_firebase_are_deleted(): void
    {
        $sender = $this->fakeSender(['expired-token']);
        $device = $this->createDevice('buyer-3');
        $subscription = $this->createSubscription($device, 'expired-token');
        $order = $this->createOrder('BA-PUSH-EXPIRED');
        $device->orders()->attach($order);

        $this->app->make(PushNotificationService::class)->notifyCustomerOfStatusChange($order->id);

        $this->assertModelMissing($subscription);
    }

    public function test_order_events_defer_new_order_and_status_notifications(): void
    {
        $notifications = $this->mock(PushNotificationService::class);
        $notifications->shouldReceive('notifyAdminsOfNewOrder')->once();
        $notifications->shouldReceive('notifyCustomerOfStatusChange')->once();

        $order = $this->createOrder('BA-PUSH-EVENT');
        $order->update(['status' => 'diproses']);

        defer()->invoke();
    }

    public function test_admin_status_update_notifies_the_buyer_device_with_a_link_to_the_order(): void
    {
        $sender = $this->fakeSender();
        $buyerDevice = $this->createDevice('buyer-after-checkout');
        $this->createSubscription($buyerDevice, 'buyer-after-checkout-token');
        $order = $this->createOrder('BA-PUSH-VERIFIED');
        $buyerDevice->orders()->attach($order);
        $admin = User::factory()->create(['role' => User::ROLE_INTI]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'diproses'])
            ->assertRedirect();

        defer()->invoke();

        $notification = collect($sender->calls)->firstWhere('title', 'Pesanan sedang diproses');

        $this->assertSame(['buyer-after-checkout-token'], $notification['tokens']);
        $this->assertSame(route('orders.show', ['orderCode' => 'BA-PUSH-VERIFIED']), $notification['url']);
    }

    #[TestWith(['menunggu_verifikasi', 'Pembayaran sedang diperiksa', 'Panitia sedang memeriksa pembayaran Anda. · BA-PUSH-TEXT'])]
    #[TestWith(['diproses', 'Pesanan sedang diproses', 'Pembayaran sudah diterima dan pesanan sedang disiapkan. · BA-PUSH-TEXT'])]
    #[TestWith(['siap_diantar', 'Pesanan siap diantar', 'Pesanan Anda segera diantar oleh panitia. · BA-PUSH-TEXT'])]
    #[TestWith(['selesai', 'Pesanan selesai', 'Terima kasih sudah berbelanja sambil beramal. · BA-PUSH-TEXT'])]
    #[TestWith(['batal', 'Pesanan dibatalkan', 'Hubungi panitia jika ada pertanyaan. · BA-PUSH-TEXT'])]
    public function test_status_notification_text_matches_the_order_status(string $status, string $title, string $body): void
    {
        $sender = $this->fakeSender();
        $order = $this->createOrder('BA-PUSH-TEXT');
        $order->updateQuietly(['status' => $status]);

        $this->app->make(PushNotificationService::class)->notifyCustomerOfStatusChange($order->id);

        $this->assertSame($title, $sender->calls[0]['title']);
        $this->assertSame($body, $sender->calls[0]['body']);
    }

    public function test_buyer_cancelling_their_own_order_receives_no_notification_but_admin_cancelling_notifies(): void
    {
        $sender = $this->fakeSender();
        $deviceToken = str_repeat('k', 64);
        $buyerDevice = Device::query()->create([
            'token_hash' => DeviceIdentity::hashToken($deviceToken),
            'last_seen_at' => now(),
        ]);
        $this->createSubscription($buyerDevice, 'buyer-cancel-token');

        $ownOrder = $this->createOrder('BA-PUSH-SELF-CANCEL');
        $buyerDevice->orders()->attach($ownOrder);

        $this->withCredentials()
            ->withCookie(DeviceIdentity::COOKIE_NAME, $deviceToken)
            ->post(route('orders.cancel', ['orderCode' => 'BA-PUSH-SELF-CANCEL']))
            ->assertRedirect(route('products.index', ['bazar' => 'menu']));

        defer()->invoke();

        $this->assertSame('batal', $ownOrder->fresh()->status);
        $this->assertNull(collect($sender->calls)->firstWhere('title', 'Pesanan dibatalkan'));

        $adminCancelledOrder = $this->createOrder('BA-PUSH-ADMIN-CANCEL');
        $buyerDevice->orders()->attach($adminCancelledOrder);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_INTI]))
            ->patch(route('admin.orders.status', $adminCancelledOrder), ['status' => 'batal'])
            ->assertRedirect();

        defer()->invoke();

        $this->assertSame(['buyer-cancel-token'], collect($sender->calls)->firstWhere('title', 'Pesanan dibatalkan')['tokens']);
    }

    public function test_status_notification_skips_an_admin_device_linked_to_the_order(): void
    {
        $sender = $this->fakeSender();
        $adminDevice = $this->createDevice('admin-with-order');
        $buyerDevice = $this->createDevice('buyer-with-order');
        $this->createSubscription($adminDevice, 'admin-token', User::factory()->create(['role' => User::ROLE_INTI]));
        $this->createSubscription($buyerDevice, 'buyer-token');
        $order = $this->createOrder('BA-PUSH-NO-ADMIN');
        $adminDevice->orders()->attach($order);
        $buyerDevice->orders()->attach($order);

        $this->app->make(PushNotificationService::class)->notifyCustomerOfStatusChange($order->id);

        $this->assertSame(['buyer-token'], $sender->calls[0]['tokens']);
    }

    public function test_checkout_notifies_the_registered_admin_device_after_the_response(): void
    {
        $sender = $this->fakeSender();
        $admin = User::factory()->create(['role' => User::ROLE_INTI]);
        $this->createSubscription($this->createDevice('admin-for-checkout'), 'admin-checkout-token', $admin);
        $this->seed();
        app(CartService::class)->add(Product::query()->where('slug', 'tote-bag-kanvas-amal')->firstOrFail(), 1);
        config(['bazar.cash_payment_enabled' => true]);

        Livewire::test(CheckoutForm::class)
            ->set('full_name', 'Pembeli Baru')
            ->set('phone', '081234567890')
            ->set('delivery_district', 'Cerme')
            ->set('delivery_village_id', $this->configuredVillageId())
            ->set('delivery_address', 'Jl. Melati No. 8')
            ->set('delivery_date', '2026-12-16')
            ->set('payment_method', 'tunai')
            ->call('submit')
            ->assertRedirect();

        defer()->invoke();

        $notification = collect($sender->calls)->firstWhere('title', 'Pesanan baru dari Pembeli Baru');

        $this->assertSame(['admin-checkout-token'], $notification['tokens']);
        $this->assertSame(route('admin.orders.show', Order::query()->sole()), $notification['url']);
    }

    public function test_new_order_without_a_registered_admin_device_logs_a_warning(): void
    {
        $this->fakeSender();
        Log::spy();
        $order = $this->createOrder('BA-PUSH-NO-DEVICE');

        $this->app->make(PushNotificationService::class)->notifyAdminsOfNewOrder($order->id);

        Log::shouldHaveReceived('warning')->once()->with(
            'New order notification skipped: no admin device is registered.',
            ['order_code' => 'BA-PUSH-NO-DEVICE'],
        );
    }

    private function configuredVillageId(): string
    {
        Setting::setMany(['delivery_rate_per_km' => '2000', 'delivery_min_fee' => '5000', 'delivery_fee_rounding' => '1000']);

        $village = DeliveryVillage::query()->where('district', 'Cerme')->firstOrFail();
        $village->update(['distance_km' => 5]);

        return (string) $village->id;
    }

    private function fakeSender(array $invalidTokens = []): FakePushSender
    {
        $sender = new FakePushSender($invalidTokens);
        $this->app->instance(PushSender::class, $sender);

        return $sender;
    }

    private function createDevice(string $identifier): Device
    {
        return Device::query()->create([
            'token_hash' => DeviceIdentity::hashToken(str_pad($identifier, 32, '-')),
            'last_seen_at' => now(),
        ]);
    }

    private function createSubscription(Device $device, string $token, ?User $user = null): PushSubscription
    {
        return PushSubscription::query()->create([
            'device_id' => $device->id,
            'user_id' => $user?->id,
            'token_hash' => PushSubscription::hashToken($token),
            'token' => $token,
            'platform' => 'desktop',
            'enabled' => true,
            'last_seen_at' => now(),
        ]);
    }

    private function createOrder(string $code, string $buyerType = 'umum'): Order
    {
        return Order::query()->create([
            'order_code' => $code,
            'buyer_type' => $buyerType,
            'institution' => $buyerType === 'siswa' ? 'SMP' : null,
            'full_name' => 'Pembeli Notifikasi',
            'phone' => '081234567890',
            'pickup_method' => 'ambil_stand',
            'payment_method' => 'tunai',
            'subtotal' => 25000,
            'status' => 'menunggu_verifikasi',
        ]);
    }
}

class FakePushSender implements PushSender
{
    /** @var list<array{tokens: list<string>, title: string, body: string, url: string}> */
    public array $calls = [];

    /** @param list<string> $invalidTokens */
    public function __construct(private array $invalidTokens = []) {}

    public function send(array $tokens, string $title, string $body, string $url): array
    {
        $this->calls[] = compact('tokens', 'title', 'body', 'url');

        return $this->invalidTokens;
    }
}
