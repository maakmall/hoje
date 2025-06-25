<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->char('id', 12)->primary();
            $table->char('id_pemesanan', 12);
            $table->unsignedBigInteger('jumlah');
            $table->enum('metode', PaymentMethod::values());
            $table->enum('status', PaymentStatus::values())
                ->default(PaymentStatus::Pending);
            $table->datetime('waktu');
            $table->string('id_transaksi', 100)->nullable();
            $table->string('akun_virtual', 50)->nullable();
            $table->string('tautan', 100)->nullable();
            $table->string('link', 100)->nullable();

            $table->foreign('id_pemesanan')
                ->references('id')
                ->on('pemesanan')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
