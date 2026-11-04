<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryVillage;
use App\Models\Setting;
use App\Services\VillageDistanceService;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DeliverySettingController extends Controller
{
    public function update(Request $request, VillageDistanceService $distances): RedirectResponse
    {
        $data = $request->validate([
            'delivery_origin' => ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                if ($this->parseOrigin((string) $value) === null) {
                    $fail('Titik asal harus berupa koordinat di sekitar Gresik, contoh: -7.1019, 112.5950.');
                }
            }],
            'delivery_rate_per_km' => ['required', 'integer', 'min:0', 'max:1000000'],
            'delivery_min_fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'delivery_fee_rounding' => ['required', 'integer', 'min:0', 'max:100000'],
            'distances' => ['nullable', 'array'],
            'distances.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        [$latitude, $longitude] = $this->parseOrigin($data['delivery_origin']);
        $origin = $latitude.', '.$longitude;
        $originChanged = Setting::getValue('delivery_origin') !== $origin;

        Setting::setMany([
            'delivery_origin' => $origin,
            'delivery_rate_per_km' => (string) $data['delivery_rate_per_km'],
            'delivery_min_fee' => (string) $data['delivery_min_fee'],
            'delivery_fee_rounding' => (string) $data['delivery_fee_rounding'],
        ]);

        DeliveryVillage::query()
            ->whereKey(array_keys($data['distances'] ?? []))
            ->get()
            ->each(fn (DeliveryVillage $village) => $village->update([
                'distance_km' => $data['distances'][$village->getKey()],
            ]));

        if ($originChanged) {
            return $this->recalculateDistances($distances, $latitude, $longitude, 'Pengaturan ongkir tersimpan. ');
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan ongkir tersimpan.');
    }

    public function recalculate(VillageDistanceService $distances): RedirectResponse
    {
        $origin = $this->parseOrigin((string) Setting::getValue('delivery_origin'));

        if ($origin === null) {
            return redirect()->route('admin.settings.edit')->with('error', 'Isi dan simpan titik asal pengantaran terlebih dahulu.');
        }

        return $this->recalculateDistances($distances, $origin[0], $origin[1]);
    }

    private function recalculateDistances(VillageDistanceService $distances, string $latitude, string $longitude, string $prefix = ''): RedirectResponse
    {
        $redirect = redirect()->route('admin.settings.edit');

        try {
            $result = $distances->recalculateFrom($latitude, $longitude);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            return $redirect->with('error', $prefix.'Jarak desa belum diperbarui karena layanan jarak tidak bisa dihubungi. Tekan Hitung Ulang Jarak atau isi jarak secara manual.');
        }

        $message = $prefix.'Jarak '.$result['updated'].' desa diperbarui.';

        if ($result['unreachable'] > 0) {
            return $redirect->with('error', $message.' '.$result['unreachable'].' desa tidak terjangkau, isi jaraknya secara manual.');
        }

        return $redirect->with('success', $message);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function parseOrigin(string $value): ?array
    {
        if (! preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $value, $matches)) {
            return null;
        }

        $isAroundGresik = $matches[1] >= -7.5 && $matches[1] <= -6.7 && $matches[2] >= 112.3 && $matches[2] <= 113.0;

        return $isAroundGresik ? [$matches[1], $matches[2]] : null;
    }
}
