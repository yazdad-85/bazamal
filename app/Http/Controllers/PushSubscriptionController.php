<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\DeviceIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, DeviceIdentity $identity): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $device = $identity->current();
        abort_unless($device, 409, 'Identitas perangkat belum tersedia. Muat ulang halaman.');

        $subscription = PushSubscription::query()->updateOrCreate([
            'token_hash' => PushSubscription::hashToken($data['token']),
        ], [
            'device_id' => $device->getKey(),
            'user_id' => $request->user()?->getKey(),
            'token' => $data['token'],
            'platform' => $data['platform'] ?? null,
            'enabled' => true,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notifikasi perangkat sudah aktif.',
        ], $subscription->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, DeviceIdentity $identity): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
        ]);

        $device = $identity->current();
        abort_unless($device, 409, 'Identitas perangkat belum tersedia. Muat ulang halaman.');

        PushSubscription::query()
            ->whereBelongsTo($device)
            ->where('token_hash', PushSubscription::hashToken($data['token']))
            ->delete();

        return response()->json([
            'message' => 'Notifikasi perangkat sudah dinonaktifkan.',
        ]);
    }
}
