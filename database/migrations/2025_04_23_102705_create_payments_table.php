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
        Schema::create('payments', function (Blueprint $table) {
            $table->char('id', 12)->primary();
            $table->char('order_id', 12);
            $table->unsignedBigInteger('amount');
            $table->enum('method', PaymentMethod::values());
            $table->enum('status', PaymentStatus::values())
                ->default(PaymentStatus::Pending);
            $table->datetime('datetime');
            $table->string('transaction_id', 100)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->string('qr_url', 100)->nullable();
            $table->string('proof', 100)->nullable();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
