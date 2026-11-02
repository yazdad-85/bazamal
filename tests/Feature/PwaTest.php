<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_exposes_the_manifest_and_install_button(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('href="/manifest.webmanifest"', false)
            ->assertSee('id="pwa-install-button"', false)
            ->assertSee('id="pwa-ios-instructions"', false)
            ->assertSee('id="push-notification-button"', false);
    }

    public function test_manifest_contains_installable_app_metadata(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('manifest.webmanifest')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame(['192x192', '512x512'], array_column($manifest['icons'], 'sizes'));
    }

    public function test_offline_page_and_service_worker_are_available(): void
    {
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));

        $this->assertStringContainsString(
            'Belum terhubung ke internet',
            file_get_contents(public_path('offline.html')),
        );
        $this->assertStringContainsString(
            "self.addEventListener('push'",
            file_get_contents(public_path('sw.js')),
        );
    }
}
