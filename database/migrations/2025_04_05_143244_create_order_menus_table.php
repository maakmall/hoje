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
        Schema::create('order_menus', function (Blueprint $table) {
            $table->id();
            $table->char('order_id', 12);
            $table->foreignId('menu_id');
            $table->enum('variant_beverage', VariantBeverage::values())->nullable();
            $table->integer('quantity');
            $table->integer('subtotal_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_menus');
    }
};
