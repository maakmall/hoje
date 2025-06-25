<?php

use App\Enums\VariantBeverage;
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
        Schema::create('pemesanan_menu', function (Blueprint $table) {
            $table->id();
            $table->char('id_pemesanan', 12);
            $table->foreignId('id_menu')->constrained('menu');
            $table->enum('variasi_minuman', VariantBeverage::values())->nullable();
            $table->integer('jumlah');
            $table->integer('subtotal_harga');

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
        Schema::dropIfExists('pemesanan_menu');
    }
};
