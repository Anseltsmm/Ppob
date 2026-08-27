<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: isi deskripsi untuk produk Token PLN yang sudah ter-seed
 * (tanpa deskripsi) agar tampil di bottom sheet halaman Token PLN.
 * Produk yang diubah hanya yang kolom description-nya masih kosong,
 * supaya deskripsi yang sudah diedit admin tidak tertimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $descriptions = [
            // Prabayar
            'PLN5' => 'Token listrik prabayar PLN senilai Rp 5.000.',
            'PLN10' => 'Token listrik prabayar PLN senilai Rp 10.000.',
            'PLN20' => 'Token listrik prabayar PLN senilai Rp 20.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.',
            'PLN50' => 'Token listrik prabayar PLN senilai Rp 50.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.',
            'PLN100' => 'Token listrik prabayar PLN senilai Rp 100.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.',
            'PLN200' => 'Token listrik prabayar PLN senilai Rp 200.000. Pastikan nomor meter (ID pelanggan) diisi dengan benar.',
            'PLN500' => 'Token listrik prabayar PLN senilai Rp 500.000.',
            // H2H Terbaik
            'PLNB20' => 'Token PLN 20.000 via H2H Terbaik — proses cepat dan stabil.',
            'PLNB50' => 'Token PLN 50.000 via H2H Terbaik — proses cepat dan stabil.',
            'PLNB100' => 'Token PLN 100.000 via H2H Terbaik — proses cepat dan stabil.',
            'PLNB200' => 'Token PLN 200.000 via H2H Terbaik — proses cepat dan stabil.',
            'PLNB500' => 'Token PLN 500.000 via H2H Terbaik — proses cepat dan stabil.',
            // H2H Promo
            'PLNP20' => 'Promo Token PLN 20.000 — harga spesial.',
            'PLNP50' => 'Promo Token PLN 50.000 — harga spesial.',
            'PLNP100' => 'Promo Token PLN 100.000 — harga spesial.',
            'PLNP200' => 'Promo Token PLN 200.000 — harga spesial.',
            'PLNP500' => 'Promo Token PLN 500.000 — harga spesial.',
            // H2H Full Reply
            'PLNF20' => 'Token PLN Full Reply 20.000 — detail lengkap.',
            'PLNF50' => 'Token PLN Full Reply 50.000 — detail lengkap.',
            'PLNF100' => 'Token PLN Full Reply 100.000 — detail lengkap.',
            'PLNF200' => 'Token PLN Full Reply 200.000 — detail lengkap.',
            'PLNF500' => 'Token PLN Full Reply 500.000 — detail lengkap.',
            // H2H Racikan
            'PLNZ20' => 'Token PLN Racikan 20.000 — harga hemat.',
            'PLNZ50' => 'Token PLN Racikan 50.000 — harga hemat.',
            'PLNZ100' => 'Token PLN Racikan 100.000 — harga hemat.',
            'PLNZ200' => 'Token PLN Racikan 200.000 — harga hemat.',
            'PLNZ500' => 'Token PLN Racikan 500.000 — harga hemat.',
        ];

        foreach ($descriptions as $code => $description) {
            DB::table('products')
                ->where('code', $code)
                ->where(function ($q) {
                    $q->whereNull('description')->orWhere('description', '');
                })
                ->update(['description' => $description]);
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->whereIn('code', ['PLN20', 'PLN50', 'PLN100', 'PLN200'])
            ->update(['description' => null]);
    }
};
