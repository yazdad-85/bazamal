<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kaos Kebaikan',
                'description' => 'Kaos katun berkualitas. 100% keuntungan untuk donasi panti asuhan.',
                'price' => 75000,
                'stock' => 40,
                'bazar_type' => 'menu',
            ],
            [
                'name' => 'Tote Bag Kanvas Amal',
                'description' => 'Tote bag kanvas siap pakai. Cocok untuk siswa dan umum.',
                'price' => 75000,
                'stock' => 12,
                'bazar_type' => 'menu',
            ],
            [
                'name' => 'Paket Sembako Amal',
                'description' => 'Paket sembako lengkap untuk berbagi kebaikan.',
                'price' => 150000,
                'stock' => 20,
                'bazar_type' => 'menu',
            ],
            [
                'name' => 'Infak & Sedekah',
                'description' => 'Infak dan sedekah untuk kegiatan amal. Tetap dapat dipesan setelah menu bazar ditutup.',
                'price' => 10000,
                'stock' => 9999,
                'bazar_type' => 'infak',
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'slug' => Str::slug($data['name']),
                    'is_active' => true,
                ])
            );
        }
    }
}
