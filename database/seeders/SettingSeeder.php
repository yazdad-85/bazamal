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
            'meta_description' => 'Belanja di Bazar Amal, toko amal untuk siswa MTs, SMP, MA, SMA, SMK, dan masyarakat. Setiap pembelian mendukung kegiatan amal pendidikan.',
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
