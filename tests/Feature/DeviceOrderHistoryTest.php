<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Order;
use App\Services\DeviceIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_creates_an_anonymous_device_cookie(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertCookie(DeviceIdentity::COOKIE_NAME);

        $this->assertDatabaseCount('devices', 1);
    }

    public function test_order_history_survives_when_the_session_is_cleared(): void
    {
        $token = str_repeat('a', 64);
        $device = $this->createDevice($token);
        $order = $this->createOrder('BA-DEVICE-1');
        $device->orders()->attach($order);

        $this->withCookie(DeviceIdentity::COOKIE_NAME, $token)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);

        $this->app['session']->flush();

        $this->withCookie(DeviceIdentity::COOKIE_NAME, $token)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);
    }

    public function test_another_device_cannot_open_an_order_without_lookup(): void
    {
        $firstToken = str_repeat('a', 64);
        $secondToken = str_repeat('b', 64);
        $firstDevice = $this->createDevice($firstToken);
        $this->createDevice($secondToken);
        $order = $this->createOrder('BA-DEVICE-2');
        $firstDevice->orders()->attach($order);

        $this->withCookie(DeviceIdentity::COOKIE_NAME, $secondToken)
            ->get(route('orders.show', ['orderCode' => $order->order_code]))
            ->assertRedirect(route('orders.index'));
    }

    public function test_successful_lookup_adds_the_order_to_the_current_device(): void
    {
        $token = str_repeat('c', 64);
        $device = $this->createDevice($token);
        $order = $this->createOrder('BA-DEVICE-3');

        $this->withCookie(DeviceIdentity::COOKIE_NAME, $token)
            ->post(route('orders.lookup'), [
                'order_code' => strtolower($order->order_code),
                'full_name' => $order->full_name,
            ])
            ->assertRedirect(route('orders.show', ['orderCode' => $order->order_code]));

        $this->assertDatabaseHas('device_order', [
            'device_id' => $device->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_legacy_session_history_is_migrated_to_the_current_device(): void
    {
        $token = str_repeat('d', 64);
        $device = $this->createDevice($token);
        $order = $this->createOrder('BA-DEVICE-4');

        $this->withSession([
            'my_orders' => [$order->order_code],
            'order_access.'.$order->order_code => true,
        ])->withCookie(DeviceIdentity::COOKIE_NAME, $token)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->order_code);

        $this->assertDatabaseHas('device_order', [
            'device_id' => $device->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_customer_can_delete_the_history_for_the_current_device(): void
    {
        $token = str_repeat('e', 64);
        $device = $this->createDevice($token);
        $order = $this->createOrder('BA-DEVICE-5');
        $device->orders()->attach($order);

        $this->withCookie(DeviceIdentity::COOKIE_NAME, $token)
            ->delete(route('orders.device.destroy'))
            ->assertRedirect(route('home'))
            ->assertCookieExpired(DeviceIdentity::COOKIE_NAME);

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
        $this->assertDatabaseMissing('device_order', [
            'device_id' => $device->id,
            'order_id' => $order->id,
        ]);
    }

    private function createDevice(string $token): Device
    {
        return Device::query()->create([
            'token_hash' => DeviceIdentity::hashToken($token),
            'last_seen_at' => now(),
        ]);
    }

    private function createOrder(string $code): Order
    {
        return Order::query()->create([
            'order_code' => $code,
            'buyer_type' => 'umum',
            'full_name' => 'Pembeli Uji',
            'phone' => '081234567890',
            'pickup_method' => 'ambil_stand',
            'payment_method' => 'tunai',
            'subtotal' => 25000,
            'status' => 'menunggu_verifikasi',
        ]);
    }
}
