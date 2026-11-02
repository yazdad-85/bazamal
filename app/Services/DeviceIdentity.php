<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Order;
use Illuminate\Support\Facades\Cookie;

class DeviceIdentity
{
    public const COOKIE_NAME = 'bazamal_device';

    public const COOKIE_MINUTES = 60 * 24 * 365 * 2;

    private ?Device $device = null;

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function set(Device $device): void
    {
        $this->device = $device;
    }

    public function current(): ?Device
    {
        return $this->device;
    }

    public function attach(Order $order): void
    {
        $this->device?->orders()->syncWithoutDetaching([$order->getKey()]);
    }

    public function canAccess(Order $order): bool
    {
        return $this->device?->orders()->whereKey($order->getKey())->exists() ?? false;
    }

    public function forget(): void
    {
        $this->device?->delete();
        $this->device = null;

        Cookie::expire(self::COOKIE_NAME, '/', config('session.domain'));
    }
}
