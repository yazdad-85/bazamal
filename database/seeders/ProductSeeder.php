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
                'bazar_type' => 'besar',
            ],
            [
                'name' => 'Tote Bag Kanvas Amal',
                'description' => 'Tote bag kanvas siap pakai. Cocok untuk siswa dan umum.',
                'price' => 75000,
                'stock' => 12,
                'bazar_type' => 'besar',
            ],
            [
                'name' => 'Paket Sembako Amal',
                'description' => 'Paket sembako lengkap untuk berbagi kebaikan.',
                'price' => 150000,
                'stock' => 20,
                'bazar_type' => 'besar',
            ],
            [
                'name' => 'Snack Ringan Peduli',
                'description' => 'Camilan ringan untuk jajan di bazar kecil.',
                'price' => 10000,
                'stock' => 100,
                'bazar_type' => 'kecil',
            ],
            [
                'name' => 'Minuman Segar Amal',
                'description' => 'Minuman dingin siap saji.',
                'price' => 8000,
                'stock' => 80,
                'bazar_type' => 'kecil',
            ],
            [
                'name' => 'Pulpen & Buku Catatan',
                'description' => 'Paket alat tulis merchandise bazar amal.',
                'price' => 15000,
                'stock' => 60,
                'bazar_type' => 'kecil',
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
