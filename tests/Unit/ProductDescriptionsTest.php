<?php

namespace Tests\Unit;

use App\Support\ProductDescriptions;
use PHPUnit\Framework\TestCase;

class ProductDescriptionsTest extends TestCase
{
    public function test_pulsa_reguler(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Telkomsel 5.000',
            'operator' => 'Telkomsel',
            'category' => 'Pulsa',
        ]);

        $this->assertStringContainsString('Pulsa Telkomsel senilai Rp 5.000', $desc);
        $this->assertStringContainsString('nomor tujuan', $desc);
    }

    public function test_sms_telepon(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => '300 SMS + 100 SMS Opt lain 30 Hari',
            'operator' => 'SMS Telepon Indosat',
            'category' => 'Pulsa',
        ]);

        // Operator dibersihkan dari kata "SMS Telepon", denom pakai satuan
        $this->assertStringContainsString('Paket SMS/Telepon Indosat (300 SMS)', $desc);
    }

    public function test_masa_aktif_brand_dari_nama(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Masa Aktif Telkomsel + 30 Hari',
            'operator' => '+Masa Aktif', // katalog hanya berisi ini
            'category' => 'Pulsa',
        ]);

        $this->assertStringContainsString('Perpanjangan masa aktif nomor Telkomsel selama 30 Hari', $desc);
    }

    public function test_paket_data(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Smart 30GB All + 60GB (01-05) 30 Hari',
            'operator' => 'Data Smart Combo',
            'category' => 'Paket Data',
        ]);

        $this->assertStringContainsString('Paket data Smart Combo — 30 GB', $desc);
    }

    public function test_token_pln(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Token PLN 200.000',
            'operator' => 'Token PLN Prabayar',
            'category' => 'Token PLN',
        ]);

        $this->assertStringContainsString('Token listrik prabayar PLN senilai Rp 200.000', $desc);
        $this->assertStringContainsString('nomor meter', $desc);
    }

    public function test_ewallet_fixed(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'GRABPAY Penumpang 20.000',
            'operator' => 'Top Up Saldo GRAB',
            'category' => 'E-Wallet',
        ]);

        $this->assertStringContainsString('Top up saldo GRAB senilai Rp 20.000', $desc);
    }

    public function test_ewallet_open_denom(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Dana Topup (Bebas Nominal)',
            'operator' => 'Bebas Nominal Uang Elektronik',
            'category' => 'E-Wallet',
            'open_denom' => true,
        ]);

        $this->assertStringContainsString('DANA', $desc);
        $this->assertStringContainsString('nominal bebas', $desc);
    }

    public function test_game(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Gemscool 1000 G-Cash',
            'operator' => 'TPG Game Vcr Gemscool',
            'category' => 'Game',
        ]);

        $this->assertStringContainsString('Voucher Gemscool', $desc);
        $this->assertStringContainsString('User ID', $desc);
    }

    public function test_pascabayar(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'Bayar PDAM Kabupaten Gresik',
            'operator' => 'PDAM Jawa Timur',
            'category' => 'Pascabayar',
            'inquiry_code' => 'CPAM...',
        ]);

        $this->assertStringContainsString('Pembayaran tagihan: Bayar PDAM Kabupaten Gresik', $desc);
    }

    public function test_token_pln_satuan_kata(): void
    {
        // "1 Juta" harus jadi Rp 1.000.000, bukan Rp 1
        $desc = ProductDescriptions::forItem([
            'name' => 'Token PLN 1 Juta',
            'operator' => 'Token PLN Prabayar',
            'category' => 'Token PLN',
        ]);

        $this->assertStringContainsString('senilai Rp 1.000.000', $desc);
        $this->assertDoesNotMatchRegularExpression('/senilai Rp \d{1,3}\.\s/', $desc);
    }

    public function test_token_pln_satuan_ribu(): void
    {
        // "20 Ribu" harus jadi Rp 20.000, bukan Rp 20
        $desc = ProductDescriptions::forItem([
            'name' => 'Promo Token PLN 20 Ribu',
            'operator' => 'Token PLN Prabayar',
            'category' => 'Token PLN',
        ]);

        $this->assertStringContainsString('senilai Rp 20.000', $desc);
        $this->assertDoesNotMatchRegularExpression('/senilai Rp \d{1,3}\.\s/', $desc);
    }

    public function test_token_pln_digit_dalam_kata_h2h(): void
    {
        // "H2H" punya digit 2 di dalam kata — harus tidak tertangkap; nominal dari "20.000"
        $desc = ProductDescriptions::forItem([
            'name' => 'H2H Token PLN 20.000',
            'operator' => 'H2H Token PLN',
            'category' => 'Token PLN',
        ]);

        $this->assertStringContainsString('senilai Rp 20.000', $desc);
        $this->assertDoesNotMatchRegularExpression('/senilai Rp \d{1,3}\.\s/', $desc);
    }

    public function test_ewallet_satuan_singkatan_rb(): void
    {
        // "100rb" (singkatan tanpa spasi) harus jadi Rp 100.000, bukan Rp 100
        $desc = ProductDescriptions::forItem([
            'name' => 'Voucher Grab Food 100rb',
            'operator' => 'Top Up Saldo GRAB',
            'category' => 'E-Wallet',
        ]);

        $this->assertStringContainsString('senilai Rp 100.000', $desc);
        $this->assertDoesNotMatchRegularExpression('/senilai Rp \d{1,3}\.\s/', $desc);
    }

    public function test_cetak_voucher(): void
    {
        // Harus deskripsi voucher, bukan pulsa/SMS — meski nama mengandung "Telp"
        $desc = ProductDescriptions::forItem([
            'name' => 'Act Voucher 90GB All+Unl App+Telp+SMS 24 jam 28 Hari',
            'operator' => 'Isat Cetak Voucher Unli',
            'category' => 'Cetak Voucher',
        ]);

        $this->assertStringContainsString('Voucher Indosat', $desc);
        $this->assertStringContainsString('kode voucher (SN)', $desc);
        $this->assertStringNotContainsString('Paket SMS/Telepon', $desc);
        $this->assertStringNotContainsString('Pulsa Isat', $desc);
    }

    public function test_pulsa_transfer(): void
    {
        $desc = ProductDescriptions::forItem([
            'name' => 'BYU 15.000',
            'operator' => 'PULSA BYU DIRECT',
            'category' => 'Pulsa Transfer',
        ]);

        $this->assertStringContainsString('Transfer pulsa PULSA BYU DIRECT senilai Rp 15.000', $desc);
    }
}
