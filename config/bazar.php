<?php

return [
    'admin_whatsapp' => env('ADMIN_WHATSAPP', '6281234567890'),
    'bank_info' => env('BAZAR_BANK_INFO', 'BSI 7123456789 a.n. Panitia Bazar Amal'),
    'menu_order_deadline' => '2026-11-30',
    'menu_delivery_dates' => [
        '2026-12-16' => 'Rabu, 16 Desember 2026',
        '2026-12-17' => 'Kamis, 17 Desember 2026',
        '2026-12-18' => 'Jumat, 18 Desember 2026',
    ],
    // Ubah menjadi true jika Tunai (Cash/COD) perlu ditawarkan lagi di checkout.
    'cash_payment_enabled' => false,
    // Titik asal pengantaran bawaan: YASMU, Jl. Kyai Sahlan I No. 24, Manyarejo.
    'delivery_origin_default' => '-7.120482421350316, 112.60268040851332',
    // Alamat halaman masuk panitia. Jangan memakai kata login.
    'login_path' => 'yasm-gerbang',
];
