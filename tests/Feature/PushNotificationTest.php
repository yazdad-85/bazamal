<?php

namespace Tests\Feature;

use App\Contracts\PushSender;
use App\Models\Device;
use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\DeviceIdentity;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
        $this->assertSame('Pesanan baru masuk', $sender->calls[0]['title']);
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
        $this->assertSame('Status pesanan diperbarui', $sender->calls[0]['title']);
        $this->assertStringContainsString($order->status_label, $sender->calls[0]['body']);
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

        $notification = collect($sender->calls)->firstWhere('title', 'Status pesanan diperbarui');

        $this->assertSame(['buyer-after-checkout-token'], $notification['tokens']);
        $this->assertSame('BA-PUSH-VERIFIED sekarang Diproses.', $notification['body']);
        $this->assertSame(route('orders.show', ['orderCode' => 'BA-PUSH-VERIFIED']), $notification['url']);
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
