<?php

namespace App\Models;

use App\Support\Institutions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const CACHE_KEY = 'app_settings';

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $all = static::allCached();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::clearCache();
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        static::clearCache();
    }

    public static function allCached(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    public static function siteName(): string
    {
        return static::getValue('site_name', config('app.name', 'Bazar Amal')) ?: 'Bazar Amal';
    }

    public static function siteLogoUrl(): ?string
    {
        $path = static::getValue('site_logo');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $version = Storage::disk('public')->lastModified($path);

        return asset('storage/'.$path).'?v='.$version;
    }

    public static function qrisUrl(): ?string
    {
        $path = static::getValue('qris_image');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $version = Storage::disk('public')->lastModified($path);

        return asset('storage/'.$path).'?v='.$version;
    }

    public static function bankInfo(): string
    {
        return static::getValue('bank_info', config('bazar.bank_info')) ?: '';
    }

    public static function donationAmount(): int
    {
        return (int) Order::query()
            ->where('status', '!=', 'batal')
            ->sum('subtotal');
    }

    public static function donationTotal(): string
    {
        return number_format(static::donationAmount(), 0, ',', '.');
    }

    public static function bendaharaWhatsApp(): string
    {
        return preg_replace('/\D+/', '', (string) static::getValue(
            'wa_bendahara',
            config('bazar.admin_whatsapp', '6281234567890')
        )) ?: '';
    }

    public static function institutionWhatsApp(?string $institution): string
    {
        if (! $institution || ! in_array($institution, Institutions::options(), true)) {
            return static::bendaharaWhatsApp();
        }

        $key = 'wa_'.strtolower($institution);
        $number = preg_replace('/\D+/', '', (string) static::getValue($key, ''));

        return $number !== '' ? $number : static::bendaharaWhatsApp();
    }

    public static function whatsappForOrder(Order $order): string
    {
        if ($order->buyer_type === 'siswa') {
            return static::institutionWhatsApp($order->institution);
        }

        return static::bendaharaWhatsApp();
    }
}
