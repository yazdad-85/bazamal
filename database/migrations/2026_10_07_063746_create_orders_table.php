<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->enum('buyer_type', ['umum', 'siswa']);
            $table->string('institution')->nullable();
            $table->string('class_name')->nullable();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->enum('pickup_method', ['ambil_stand', 'antar_kelas', 'kirim_alamat']);
            $table->enum('payment_method', ['online', 'transfer', 'tunai']);
            $table->string('payment_proof')->nullable();
            $table->unsignedInteger('subtotal');
            $table->enum('status', [
                'menunggu_verifikasi',
                'diproses',
                'siap_diantar',
                'selesai',
                'batal',
            ])->default('menunggu_verifikasi');
            $table->timestamps();

            $table->index(['buyer_type', 'institution', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
