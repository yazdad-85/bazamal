<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminManifestController extends Controller
{
    /**
     * Serve the installable admin app manifest, which opens on the hidden login path.
     */
    public function __invoke(): JsonResponse
    {
        $loginPath = '/'.trim((string) config('bazar.login_path', 'yasm-gerbang'), '/');

        return response()->json([
            'id' => $loginPath,
            'name' => 'Admin Bazar Amal YASMU',
            'short_name' => 'Admin Bazar',
            'description' => 'Kelola pesanan Bazar Amal YASMU.',
            'start_url' => $loginPath,
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#f5f5f4',
            'theme_color' => '#145214',
            'lang' => 'id',
            'icons' => [
                ['src' => '/icons/icon-192.png?v=2', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => '/icons/icon-512.png?v=2', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ], headers: ['Content-Type' => 'application/manifest+json']);
    }
}
