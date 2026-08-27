<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice')->unique()->comment('merchant_ref di Tripay');
            $table->string('reference')->nullable()->comment('Nomor referensi transaksi Tripay');
            $table->decimal('amount', 15, 2)->comment('Nominal topup');
            $table->decimal('fee_customer', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->comment('Nominal + fee');
            $table->string('payment_method')->nullable();
            $table->string('payment_name')->nullable();
            $table->string('pay_code')->nullable();
            $table->string('pay_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->string('status')->default('UNPAID')->comment('UNPAID, PAID, EXPIRED, FAILED');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
