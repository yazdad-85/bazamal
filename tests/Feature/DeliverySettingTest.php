<?php

namespace Tests\Feature;

use App\Models\DeliveryVillage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DeliverySettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_changing_the_origin_saves_fees_and_recalculates_distances(): void
    {
        $this->fakeRouter();

        $this->actingAs($this->inti())
            ->put(route('admin.settings.delivery.update'), $this->payload())
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success', 'Pengaturan ongkir tersimpan. Jarak 45 desa diperbarui.');

        $this->assertSame('-7.1019, 112.5950', Setting::getValue('delivery_origin'));
        $this->assertSame('2000', Setting::getValue('delivery_rate_per_km'));
        $this->assertSame('5000', Setting::getValue('delivery_min_fee'));
        $this->assertSame('1000', Setting::getValue('delivery_fee_rounding'));
        $this->assertSame(0, DeliveryVillage::query()->whereNull('distance_km')->count());
        $this->assertSame('1.50', DeliveryVillage::query()->orderBy('id')->first()->distance_km);

        Http::assertSent(fn (HttpRequest $request): bool => str_contains($request->url(), '/table/v1/driving/112.5950,-7.1019;')
            && str_contains($request->url(), 'sources=0'));
    }

    public function test_manual_distance_is_kept_when_the_origin_is_unchanged(): void
    {
        Setting::setValue('delivery_origin', '-7.1019, 112.5950');
        $village = DeliveryVillage::query()->firstOrFail();

        $this->actingAs($this->inti())
            ->put(route('admin.settings.delivery.update'), $this->payload([
                'distances' => [$village->id => '7.25'],
            ]))
            ->assertSessionHas('success', 'Pengaturan ongkir tersimpan.');

        $this->assertSame('7.25', $village->fresh()->distance_km);
        Http::assertNothingSent();
    }

    public function test_router_failure_keeps_existing_distances_and_warns_the_admin(): void
    {
        Setting::setValue('delivery_origin', '-7.1019, 112.5950');
        $village = DeliveryVillage::query()->firstOrFail();
        $village->update(['distance_km' => 4]);
        Http::fake(['*' => Http::response('down', 503)]);

        $this->actingAs($this->inti())
            ->post(route('admin.settings.delivery.recalculate'))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('error', 'Jarak desa belum diperbarui karena layanan jarak tidak bisa dihubungi. Tekan Hitung Ulang Jarak atau isi jarak secara manual.');

        $this->assertSame('4.00', $village->fresh()->distance_km);
    }

    public function test_unreachable_village_keeps_its_distance_and_is_reported(): void
    {
        Setting::setValue('delivery_origin', '-7.1019, 112.5950');
        $village = DeliveryVillage::query()->orderBy('id')->firstOrFail();
        $village->update(['distance_km' => 4]);
        $this->fakeRouter(unreachableIndex: 1);

        $this->actingAs($this->inti())
            ->post(route('admin.settings.delivery.recalculate'))
            ->assertSessionHas('error', 'Jarak 44 desa diperbarui. 1 desa tidak terjangkau, isi jaraknya secara manual.');

        $this->assertSame('4.00', $village->fresh()->distance_km);
    }

    public function test_recalculating_without_an_origin_shows_an_error(): void
    {
        $this->actingAs($this->inti())
            ->post(route('admin.settings.delivery.recalculate'))
            ->assertSessionHas('error', 'Isi dan simpan titik asal pengantaran terlebih dahulu.');

        Http::assertNothingSent();
    }

    #[TestWith(['112.5950, -7.1019'])]
    #[TestWith(['-6.2, 106.8'])]
    #[TestWith(['bukan koordinat'])]
    public function test_origin_outside_the_gresik_area_is_rejected(string $origin): void
    {
        $this->actingAs($this->inti())
            ->put(route('admin.settings.delivery.update'), $this->payload(['delivery_origin' => $origin]))
            ->assertSessionHasErrors(['delivery_origin' => 'Titik asal harus berupa koordinat di sekitar Gresik, contoh: -7.1019, 112.5950.']);

        $this->assertNull(Setting::getValue('delivery_origin'));
    }

    public function test_coordinator_cannot_change_delivery_settings(): void
    {
        $coordinator = User::factory()->create(['role' => User::ROLE_KOORDINATOR, 'institution' => 'SMP']);

        $this->actingAs($coordinator)
            ->put(route('admin.settings.delivery.update'), $this->payload())
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->post(route('admin.settings.delivery.recalculate'))
            ->assertForbidden();

        $this->assertNull(Setting::getValue('delivery_rate_per_km'));
    }

    public function test_settings_page_shows_the_delivery_form_with_every_village(): void
    {
        $this->actingAs($this->inti())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Ongkos Kirim Menu Bazar')
            ->assertSee('Manyar Sidomukti')
            ->assertSee('Tanjung Widoro');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'delivery_origin' => '-7.1019, 112.5950',
            'delivery_rate_per_km' => '2000',
            'delivery_min_fee' => '5000',
            'delivery_fee_rounding' => '1000',
            ...$overrides,
        ];
    }

    private function fakeRouter(?int $unreachableIndex = null): void
    {
        $distances = [0];

        foreach (range(1, DeliveryVillage::query()->count()) as $index) {
            $distances[] = $index === $unreachableIndex ? null : $index * 1500;
        }

        Http::fake(['*' => Http::response(['code' => 'Ok', 'distances' => [$distances]])]);
    }

    private function inti(): User
    {
        return User::factory()->create(['role' => User::ROLE_INTI]);
    }
}
