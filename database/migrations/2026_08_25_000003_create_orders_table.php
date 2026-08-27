<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_code')->unique()->comment('refID yang dikirim ke OkeConnect');
            $table->string('product_name');
            $table->string('destination')->comment('Nomor tujuan / ID pelanggan');
            $table->decimal('qty', 15, 2)->nullable()->comment('Nominal untuk transaksi open denom');
            $table->decimal('buy_price', 15, 2)->default(0)->comment('Harga modal');
            $table->decimal('sell_price', 15, 2)->default(0)->comment('Harga jual ke customer');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('trx_id')->nullable()->comment('Nomor transaksi T# dari OkeConnect');
            $table->string('sn')->nullable()->comment('Serial number / SN dari OkeConnect');
            $table->text('message')->nullable()->comment('Pesan mentah dari OkeConnect');
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
