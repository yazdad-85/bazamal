<?php

namespace App\Http\Middleware;

use App\Models\Device;
use App\Services\DeviceIdentity;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdentifyDevice
{
    public function __construct(private DeviceIdentity $identity) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(DeviceIdentity::COOKIE_NAME);

        if (! is_string($token) || Str::length($token) < 32) {
            $token = Str::random(64);

            Cookie::queue(
                DeviceIdentity::COOKIE_NAME,
                $token,
                DeviceIdentity::COOKIE_MINUTES,
                '/',
                config('session.domain'),
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site', 'lax'),
            );
        }

        try {
            $device = Device::firstOrCreate([
                'token_hash' => DeviceIdentity::hashToken($token),
            ], [
                'last_seen_at' => now(),
            ]);

            if (! $device->last_seen_at || $device->last_seen_at->lt(now()->subDay())) {
                $device->forceFill(['last_seen_at' => now()])->saveQuietly();
            }

            $this->identity->set($device);
        } catch (QueryException) {
            // The application must remain reachable before its migrations run.
        }

        return $next($request);
    }
}
