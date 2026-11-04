<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_method')->change();
            $table->foreignId('delivery_village_id')->nullable()->after('delivery_address')->constrained()->nullOnDelete();
            $table->string('delivery_area')->nullable()->after('delivery_village_id');
            $table->decimal('delivery_distance_km', 6, 2)->nullable()->after('delivery_area');
            $table->unsignedInteger('delivery_fee')->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_village_id');
            $table->dropColumn(['delivery_area', 'delivery_distance_km', 'delivery_fee']);
        });
    }
};
