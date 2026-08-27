<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama brand/merek, contoh: Telkomsel, Indosat, XL');
            $table->string('slug')->unique();
            $table->string('icon_font')->nullable()->comment('Nama icon Bootstrap (tanpa prefix bi-)');
            $table->string('icon_image')->nullable()->comment('Path gambar logo yang di-upload');
            $table->string('color')->nullable()->comment('Warna aksen brand (hex)');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};