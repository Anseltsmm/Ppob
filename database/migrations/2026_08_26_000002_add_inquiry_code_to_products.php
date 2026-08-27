<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('inquiry_code')->nullable()->after('code')
                ->comment('Kode produk inquiry (C...) untuk pembayaran pascabayar. Produk dengan inquiry_code = produk bayar (B...).');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('inquiry_code');
        });
    }
};
