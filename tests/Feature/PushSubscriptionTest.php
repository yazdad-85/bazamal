<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\DeviceIdentity;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_device_can_register_an_encrypted_push_token(): void
    {
        $deviceToken = str_repeat('a', 64);
        $device = $this->createDevice($deviceToken);
        $user = User::factory()->create();
        $pushToken = 'fcm-token-for-admin-device';

        $this->actingAs($user)
            ->withCredentials()
            ->withCookie(DeviceIdentity::COOKIE_NAME, $deviceToken)
            ->postJson(route('push-subscriptions.store'), [
                'token' => $pushToken,
                'platform' => 'desktop',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Notifikasi perangkat sudah aktif.');

        $subscription = PushSubscription::query()->sole();

        $this->assertSame($device->id, $subscription->device_id);
        $this->assertSame($user->id, $subscription->user_id);
        $this->assertSame($pushToken, $subscription->token);
        $this->assertSame(PushSubscription::hashToken($pushToken), $subscription->token_hash);
        $this->assertNotSame($pushToken, DB::table('push_subscriptions')->value('token'));
    }

    public function test_registration_rejects_an_invalid_push_token(): void
    {
        $deviceToken = str_repeat('b', 64);
        $this->createDevice($deviceToken);

        $this->withCredentials()
            ->withCookie(DeviceIdentity::COOKIE_NAME, $deviceToken)
            ->postJson(route('push-subscriptions.store'), [
                'token' => '',
                'platform' => str_repeat('x', 51),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token', 'platform']);

        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_device_can_remove_its_own_push_token(): void
    {
        $deviceToken = str_repeat('c', 64);
        $device = $this->createDevice($deviceToken);
        $pushToken = 'fcm-token-to-remove';
        $subscription = $this->createSubscription($device, $pushToken);

        $this->withCredentials()
            ->withCookie(DeviceIdentity::COOKIE_NAME, $deviceToken)
            ->deleteJson(route('push-subscriptions.destroy'), ['token' => $pushToken])
            ->assertOk()
            ->assertJsonPath('message', 'Notifikasi perangkat sudah dinonaktifkan.');

        $this->assertModelMissing($subscription);
    }

    public function test_device_cannot_remove_another_devices_push_token(): void
    {
        $firstDeviceToken = str_repeat('d', 64);
        $secondDeviceToken = str_repeat('e', 64);
        $firstDevice = $this->createDevice($firstDeviceToken);
        $this->createDevice($secondDeviceToken);
        $pushToken = 'fcm-token-on-another-device';
        $subscription = $this->createSubscription($firstDevice, $pushToken);

        $this->withCredentials()
            ->withCookie(DeviceIdentity::COOKIE_NAME, $secondDeviceToken)
            ->deleteJson(route('push-subscriptions.destroy'), ['token' => $pushToken])
            ->assertOk();

        $this->assertModelExists($subscription);
    }

    private function createDevice(string $token): Device
    {
        return Device::query()->create([
            'token_hash' => DeviceIdentity::hashToken($token),
            'last_seen_at' => now(),
        ]);
    }

    private function createSubscription(Device $device, string $token): PushSubscription
    {
        return PushSubscription::query()->create([
            'device_id' => $device->id,
            'token_hash' => PushSubscription::hashToken($token),
            'token' => $token,
            'platform' => 'desktop',
            'enabled' => true,
            'last_seen_at' => now(),
        ]);
    }
}
