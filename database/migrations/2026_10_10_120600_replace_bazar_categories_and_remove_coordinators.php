<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable();
        });

        DB::table('products')->where('bazar_type', 'kecil')->update(['is_active' => false]);

        DB::table('products')->update([
            'category' => DB::raw("CASE
                WHEN lower(name) LIKE '%infak%'
                    OR lower(name) LIKE '%sedekah%'
                    OR lower(name) LIKE '%sodaqoh%'
                    OR lower(name) LIKE '%shodaqoh%'
                    OR lower(name) LIKE '%sodaqah%'
                THEN 'infak'
                ELSE 'menu'
            END"),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('bazar_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('category', 'bazar_type');
        });

        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'koordinator')->update([
                'role' => 'inti',
                'institution' => null,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('products')->whereIn('bazar_type', ['menu', 'infak'])->update(['bazar_type' => 'besar']);
    }
};
