<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_expose_indexable_metadata_and_private_pages_do_not(): void
    {
        $this->seed();

        $product = Product::active()->firstOrFail();

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('name="robots" content="index, follow"', false)
            ->assertSee('Toko Amal untuk Siswa dan Masyarakat', false)
            ->assertSee('application/ld+json', false);

        $this->get('/bazar?bazar=kecil')
            ->assertOk()
            ->assertSee('Katalog Bazar Kecil', false)
            ->assertSee('rel="canonical"', false);

        $this->get('/bazar?bazar=besar&q=rahasia')
            ->assertOk()
            ->assertSee('noindex, follow', false);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name.' — ', false)
            ->assertSee('https://schema.org', false);

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('noindex, nofollow', false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_sitemap_lists_catalogs_and_active_products_only(): void
    {
        $this->seed();

        $hidden = Product::create([
            'name' => 'Produk Disembunyikan',
            'price' => 1000,
            'stock' => 1,
            'bazar_type' => 'kecil',
            'is_active' => false,
        ]);

        $active = Product::active()->firstOrFail();

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /pesanan')
            ->assertSee('/sitemap.xml');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('products.show', $active), false)
            ->assertSee('bazar=besar', false)
            ->assertSee('bazar=kecil', false)
            ->assertDontSee(route('products.show', $hidden), false);
    }
}
