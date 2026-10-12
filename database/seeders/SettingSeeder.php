<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'Bazar Amal',
            'meta_description' => 'Nikmati berbagai pilihan makanan lezat di Bazar Amal YASMU. Setiap pembelian Anda mendanai pembangunan dan fasilitas belajar siswa.',
            'site_logo' => null,
            'bank_info' => config('bazar.bank_info'),
            'qris_image' => null,
            'wa_bendahara' => config('bazar.admin_whatsapp', '6281234567890'),
            'wa_mts' => '',
            'wa_smp' => '',
            'wa_ma' => '',
            'wa_sma' => '',
            'wa_smk' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::clearCache();
    }
}
