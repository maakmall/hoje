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
        Schema::create('harga_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_menu')
                ->constrained('menu')
                ->cascadeOnDelete();
            $table->enum('variasi_minuman', VariantBeverage::values())->nullable();
            $table->integer('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_menu');
    }
};
