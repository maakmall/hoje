<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->char('id', 12)->primary();
            $table->string('nama_pelanggan', 100);
            $table->string('email_pelanggan', 50);
            $table->string('telepon_pelanggan', 15);
            $table->datetime('waktu');
            $table->foreignId('id_lokasi')->constrained('lokasi');
            $table->integer('jumlah_orang');
            $table->text('catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
