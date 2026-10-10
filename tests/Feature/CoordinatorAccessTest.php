<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinatorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_panel_users_are_panitia_and_can_see_every_institution(): void
    {
        $this->seed();
        $inti = User::where('email', 'admin@bazar.test')->firstOrFail();

        $this->actingAs($inti)
            ->post(route('admin.users.store'), [
                'name' => 'Panitia Kedua',
                'email' => 'panitia@bazar.test',
                'password' => 'Koordinator1a',
                'password_confirmation' => 'Koordinator1a',
            ])
            ->assertRedirect(route('admin.users.index'));

        $panitia = User::where('email', 'panitia@bazar.test')->firstOrFail();
        $this->assertTrue($panitia->isInti());
        $this->assertNull($panitia->institution);

        $this->makeOrder('SMA', 'Siswa SMA');
        $this->makeOrder('SMP', 'Siswa SMP');

        $this->actingAs($panitia)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Siswa SMA')
            ->assertSee('Siswa SMP');

        $this->actingAs($panitia)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('Koordinator');
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
