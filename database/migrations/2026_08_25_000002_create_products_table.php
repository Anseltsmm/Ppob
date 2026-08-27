<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->comment('Kode produk di OkeConnect, contoh: T5, S20, BBSDN');
            $table->enum('type', ['prepaid', 'opendenom'])->default('prepaid');
            $table->text('description')->nullable();
            $table->decimal('modal_price', 15, 2)->default(0)->comment('Harga modal dari OkeConnect');
            $table->decimal('sell_price', 15, 2)->default(0)->comment('Harga jual ke customer');
            $table->decimal('admin_fee', 15, 2)->default(0)->comment('Biaya admin untuk tipe opendenom');
            $table->decimal('min_nominal', 15, 2)->nullable()->comment('Nominal minimal untuk opendenom');
            $table->decimal('max_nominal', 15, 2)->nullable()->comment('Nominal maksimal untuk opendenom');
            $table->string('operator')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
