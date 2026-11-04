<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('delivery_villages')->whereIn('district', ['Sangkapura', 'Tambak'])->delete();
    }

    public function down(): void
    {
        //
    }
};
