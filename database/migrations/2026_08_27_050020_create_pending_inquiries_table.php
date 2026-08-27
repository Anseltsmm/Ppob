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
        Schema::create('pending_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id')->unique();
            $table->string('product_code', 20);
            $table->string('destination', 30);
            $table->string('user_id');
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->text('customer_name')->nullable();
            $table->text('raw')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_inquiries');
    }
};
