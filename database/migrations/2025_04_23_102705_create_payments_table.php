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
            $table->id();
            $table->foreignId('order_id');
            $table->unsignedBigInteger('amount');
            $table->enum('method', PaymentMethod::values());
            $table->enum('status', PaymentStatus::values())
                ->default(PaymentStatus::Pending);
            $table->datetime('datetime');
            $table->string('transaction_id')->nullable();
            $table->string('va_number')->nullable();
            $table->string('qr_url')->nullable();
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
