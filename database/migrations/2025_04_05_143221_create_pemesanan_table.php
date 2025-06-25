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
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->char('id', 12)->primary();
            $table->char('id_reservasi', 12)->nullable();
            $table->foreignId('id_meja')->nullable()->constrained('meja');
            $table->datetime('waktu');

            $table->foreign('id_reservasi')
                ->references('id')
                ->on('reservasi')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
