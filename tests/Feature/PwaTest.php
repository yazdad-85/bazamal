<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_login_page_links_the_admin_app_manifest_that_opens_on_the_login_path(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('admin.manifest', absolute: false).'"', false);

        $this->get(route('admin.manifest'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('start_url', '/yasm-gerbang')
            ->assertJsonPath('id', '/yasm-gerbang')
            ->assertJsonPath('display', 'standalone');
    }

    public function test_fetching_the_admin_manifest_creates_no_session_or_device(): void
    {
        $this->get(route('admin.manifest'))
            ->assertOk()
            ->assertCookieMissing(config('session.cookie'));

        $this->assertDatabaseCount('devices', 0);
    }

    public function test_opening_the_admin_app_while_signed_in_redirects_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/yasm-gerbang')
            ->assertRedirect(route('dashboard'));
    }

    public function test_storefront_shows_the_admin_panel_link_only_to_signed_in_users(): void
    {
        $this->get('/')->assertDontSee('aria-label="Panel Admin"', false);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('aria-label="Panel Admin"', false)
            ->assertSee('href="'.route('admin.dashboard').'"', false);
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
