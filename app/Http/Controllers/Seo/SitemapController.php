<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = [
            [
                'loc' => route('home'),
                'lastmod' => now()->toDateString(),
            ],
            [
                'loc' => route('products.index', ['bazar' => 'besar']),
                'lastmod' => now()->toDateString(),
            ],
            [
                'loc' => route('products.index', ['bazar' => 'kecil']),
                'lastmod' => now()->toDateString(),
            ],
        ];

        Product::query()
            ->active()
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $product) use (&$entries): void {
                $entries[] = [
                    'loc' => route('products.show', $product),
                    'lastmod' => $product->updated_at?->toDateString(),
                ];
            });

        return response()
            ->view('seo.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /profile',
            'Disallow: /dashboard',
            'Disallow: /keranjang',
            'Disallow: /checkout',
            'Disallow: /pesanan',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
