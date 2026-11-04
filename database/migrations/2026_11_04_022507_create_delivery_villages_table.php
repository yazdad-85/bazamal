<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_villages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('district');
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->timestamps();
            $table->index(['district', 'name']);
        });

        DB::table('delivery_villages')->insert(array_map(
            fn (array $village): array => [...$village, 'created_at' => now(), 'updated_at' => now()],
            $this->villages(),
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_villages');
    }

    /**
     * Villages of Kecamatan Manyar and Bungah, Kabupaten Gresik.
     * Source: emsifa/api-wilayah-indonesia and cahyadsn/wilayah (MIT), based on Kepmendagri 2025.
     *
     * @return list<array{code: string, district: string, name: string, postal_code: ?string, latitude: float, longitude: float}>
     */
    private function villages(): array
    {
        return [
            ['code' => '35.25.10.2001', 'district' => 'Manyar', 'name' => 'Tanggulrejo', 'postal_code' => '61151', 'latitude' => -7.086325, 'longitude' => 112.5380425],
            ['code' => '35.25.10.2002', 'district' => 'Manyar', 'name' => 'Morobakung', 'postal_code' => '61151', 'latitude' => -7.0574951, 'longitude' => 112.551267],
            ['code' => '35.25.10.2003', 'district' => 'Manyar', 'name' => 'Pejangganan', 'postal_code' => '61151', 'latitude' => -7.0639793, 'longitude' => 112.5502906],
            ['code' => '35.25.10.2004', 'district' => 'Manyar', 'name' => 'Gumeno', 'postal_code' => '61151', 'latitude' => -7.073683, 'longitude' => 112.555095],
            ['code' => '35.25.10.2005', 'district' => 'Manyar', 'name' => 'Sumberejo', 'postal_code' => '61151', 'latitude' => -7.084988, 'longitude' => 112.5510928],
            ['code' => '35.25.10.2006', 'district' => 'Manyar', 'name' => 'Ngampel', 'postal_code' => '61151', 'latitude' => -7.061479, 'longitude' => 112.5629199],
            ['code' => '35.25.10.2007', 'district' => 'Manyar', 'name' => 'Betoyokauman', 'postal_code' => '61151', 'latitude' => -7.0937839, 'longitude' => 112.5601386],
            ['code' => '35.25.10.2008', 'district' => 'Manyar', 'name' => 'Sembayat', 'postal_code' => '61151', 'latitude' => -7.0690529, 'longitude' => 112.5732724],
            ['code' => '35.25.10.2009', 'district' => 'Manyar', 'name' => 'Betoyoguci', 'postal_code' => '61151', 'latitude' => -7.0947741, 'longitude' => 112.5698276],
            ['code' => '35.25.10.2010', 'district' => 'Manyar', 'name' => 'Leran', 'postal_code' => '61151', 'latitude' => -7.1217646, 'longitude' => 112.5787042],
            ['code' => '35.25.10.2011', 'district' => 'Manyar', 'name' => 'Tebalo', 'postal_code' => '61151', 'latitude' => -7.1504386, 'longitude' => 112.5790298],
            ['code' => '35.25.10.2012', 'district' => 'Manyar', 'name' => 'Karangrejo', 'postal_code' => '61151', 'latitude' => -7.0701714, 'longitude' => 112.5871794],
            ['code' => '35.25.10.2013', 'district' => 'Manyar', 'name' => 'Banyuwangi', 'postal_code' => '61151', 'latitude' => -7.090148, 'longitude' => 112.5877699],
            ['code' => '35.25.10.2014', 'district' => 'Manyar', 'name' => 'Manyarejo', 'postal_code' => '61151', 'latitude' => -7.0807536, 'longitude' => 112.6183685],
            ['code' => '35.25.10.2015', 'district' => 'Manyar', 'name' => 'Manyar Sidomukti', 'postal_code' => '61151', 'latitude' => -7.1095929, 'longitude' => 112.6134577],
            ['code' => '35.25.10.2016', 'district' => 'Manyar', 'name' => 'Manyar Sidorukun', 'postal_code' => '61151', 'latitude' => -7.0757681, 'longitude' => 112.6137278],
            ['code' => '35.25.10.2017', 'district' => 'Manyar', 'name' => 'Banjarsari', 'postal_code' => '61151', 'latitude' => -7.1380326, 'longitude' => 112.5871543],
            ['code' => '35.25.10.2018', 'district' => 'Manyar', 'name' => 'Suci', 'postal_code' => '61151', 'latitude' => -7.1503747, 'longitude' => 112.6017723],
            ['code' => '35.25.10.2019', 'district' => 'Manyar', 'name' => 'Paganden', 'postal_code' => '61151', 'latitude' => -7.1319966, 'longitude' => 112.5991069],
            ['code' => '35.25.10.2020', 'district' => 'Manyar', 'name' => 'Pongangan', 'postal_code' => '61151', 'latitude' => -7.1394568, 'longitude' => 112.6063419],
            ['code' => '35.25.10.2021', 'district' => 'Manyar', 'name' => 'Sukomulyo', 'postal_code' => '61151', 'latitude' => -7.1302538, 'longitude' => 112.6191771],
            ['code' => '35.25.10.2022', 'district' => 'Manyar', 'name' => 'Yosowilangun', 'postal_code' => '61151', 'latitude' => -7.1515183, 'longitude' => 112.6152029],
            ['code' => '35.25.10.2023', 'district' => 'Manyar', 'name' => 'Roomo', 'postal_code' => '61151', 'latitude' => -7.141468, 'longitude' => 112.6359718],
            ['code' => '35.25.12.2001', 'district' => 'Bungah', 'name' => 'Sidomukti', 'postal_code' => '61152', 'latitude' => -7.0279173, 'longitude' => 112.5060616],
            ['code' => '35.25.12.2002', 'district' => 'Bungah', 'name' => 'Mojopurogede', 'postal_code' => '61152', 'latitude' => -7.0314708, 'longitude' => 112.5188332],
            ['code' => '35.25.12.2003', 'district' => 'Bungah', 'name' => 'Mojopurowetan', 'postal_code' => '61152', 'latitude' => -7.0279401, 'longitude' => 112.5265285],
            ['code' => '35.25.12.2004', 'district' => 'Bungah', 'name' => 'Melirang', 'postal_code' => '61152', 'latitude' => -7.0351275, 'longitude' => 112.5387782],
            ['code' => '35.25.12.2005', 'district' => 'Bungah', 'name' => 'Sidorejo', 'postal_code' => '61152', 'latitude' => -7.0460109, 'longitude' => 112.5432766],
            ['code' => '35.25.12.2006', 'district' => 'Bungah', 'name' => 'Raciwetan', 'postal_code' => '61152', 'latitude' => -7.01493, 'longitude' => 112.5473061],
            ['code' => '35.25.12.2007', 'district' => 'Bungah', 'name' => 'Masangan', 'postal_code' => '61152', 'latitude' => -7.0405815, 'longitude' => 112.5544676],
            ['code' => '35.25.12.2008', 'district' => 'Bungah', 'name' => 'Sidokumpul', 'postal_code' => '61152', 'latitude' => -7.0318944, 'longitude' => 112.5600399],
            ['code' => '35.25.12.2009', 'district' => 'Bungah', 'name' => 'Pegundan', 'postal_code' => '61152', 'latitude' => -7.0178567, 'longitude' => 112.5592634],
            ['code' => '35.25.12.2010', 'district' => 'Bungah', 'name' => 'Sukowati', 'postal_code' => '61152', 'latitude' => -7.0526574, 'longitude' => 112.5606774],
            ['code' => '35.25.12.2011', 'district' => 'Bungah', 'name' => 'Abar-abir', 'postal_code' => '61152', 'latitude' => -7.0332065, 'longitude' => 112.569428],
            ['code' => '35.25.12.2012', 'district' => 'Bungah', 'name' => 'Bungah', 'postal_code' => '61152', 'latitude' => -7.0510558, 'longitude' => 112.5710023],
            ['code' => '35.25.12.2013', 'district' => 'Bungah', 'name' => 'Kemangi', 'postal_code' => '61152', 'latitude' => -7.0210226, 'longitude' => 112.5719265],
            ['code' => '35.25.12.2014', 'district' => 'Bungah', 'name' => 'Kisik', 'postal_code' => '61152', 'latitude' => -7.0322221, 'longitude' => 112.5791825],
            ['code' => '35.25.12.2015', 'district' => 'Bungah', 'name' => 'Gumeng', 'postal_code' => '61152', 'latitude' => -7.0109226, 'longitude' => 112.5878372],
            ['code' => '35.25.12.2016', 'district' => 'Bungah', 'name' => 'Sukorejo', 'postal_code' => '61152', 'latitude' => -7.0440484, 'longitude' => 112.5827832],
            ['code' => '35.25.12.2017', 'district' => 'Bungah', 'name' => 'Indrodelik', 'postal_code' => '61152', 'latitude' => -7.0284594, 'longitude' => 112.5899178],
            ['code' => '35.25.12.2018', 'district' => 'Bungah', 'name' => 'Bedanten', 'postal_code' => '61152', 'latitude' => -7.0424593, 'longitude' => 112.6045365],
            ['code' => '35.25.12.2019', 'district' => 'Bungah', 'name' => 'Sungonlegowo', 'postal_code' => '61152', 'latitude' => -7.0090019, 'longitude' => 112.6013934],
            ['code' => '35.25.12.2020', 'district' => 'Bungah', 'name' => 'Tanjung Widoro', 'postal_code' => '61152', 'latitude' => -7.0177382, 'longitude' => 112.6313725],
            ['code' => '35.25.12.2021', 'district' => 'Bungah', 'name' => 'Watuagung', 'postal_code' => '61152', 'latitude' => -7.0354109, 'longitude' => 112.6312514],
            ['code' => '35.25.12.2022', 'district' => 'Bungah', 'name' => 'Kramat', 'postal_code' => '61152', 'latitude' => -7.0490533, 'longitude' => 112.6461452],
        ];
    }
};
