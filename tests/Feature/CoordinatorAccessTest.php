<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinatorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_inti_can_create_a_coordinator_and_coordinator_only_sees_their_orders(): void
    {
        $this->seed();
        $inti = User::where('email', 'admin@bazar.test')->firstOrFail();

        $this->actingAs($inti)
            ->post(route('admin.users.store'), [
                'name' => 'Koordinator SMA',
                'email' => 'sma@bazar.test',
                'role' => 'koordinator',
                'institution' => 'SMA',
                'password' => 'Koordinator1a',
                'password_confirmation' => 'Koordinator1a',
            ])
            ->assertRedirect(route('admin.users.index'));

        $sma = $this->makeOrder('SMA', 'Siswa SMA');
        $smp = $this->makeOrder('SMP', 'Siswa SMP');

        $coordinator = User::where('email', 'sma@bazar.test')->firstOrFail();

        $this->actingAs($coordinator)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Siswa SMA')
            ->assertDontSee('Siswa SMP');

        $this->actingAs($coordinator)
            ->get(route('admin.orders.show', $smp))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->patch(route('admin.orders.status', $smp), ['status' => 'batal'])
            ->assertForbidden();

        $this->assertSame('diproses', $smp->fresh()->status);
        $this->assertNotNull($sma->id);
    }

    private function makeOrder(string $institution, string $name): Order
    {
        return Order::create([
            'order_code' => 'BA-TEST-'.$institution.'-'.$name,
            'buyer_type' => 'siswa',
            'institution' => $institution,
            'class_name' => 'X-A',
            'full_name' => $name,
            'phone' => '081234567890',
            'pickup_method' => 'ambil_stand',
            'payment_method' => 'tunai',
            'subtotal' => 10000,
            'status' => 'diproses',
        ]);
    }
}
