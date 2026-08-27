<?php

namespace Tests\Unit;

use App\Support\BillTypes;
use App\Support\EwalletBrands;
use App\Support\OperatorKeywords;
use PHPUnit\Framework\TestCase;

class MenuMatchingTest extends TestCase
{
    // ==================== OPERATOR KEYWORDS ====================

    public function test_keyword_operator_mencakup_produk_katalog_lengkap(): void
    {
        // Produk katalog lengkap yang operator/namanya bukan nama operator standar
        $this->assertTrue(in_array('freedoom', OperatorKeywords::keywords('Indosat'), true));
        $this->assertTrue(in_array('freedom', OperatorKeywords::keywords('Indosat'), true));
        $this->assertTrue(in_array('bronet', OperatorKeywords::keywords('AXIS'), true));
        $this->assertTrue(in_array('owsem', OperatorKeywords::keywords('AXIS'), true));
        $this->assertTrue(in_array('aigo', OperatorKeywords::keywords('AXIS'), true));
        $this->assertTrue(in_array('byu', OperatorKeywords::keywords('Telkomsel'), true));
        $this->assertTrue(in_array('voucher data combo', OperatorKeywords::keywords('Telkomsel'), true));
        $this->assertTrue(in_array('voucher unlimited', OperatorKeywords::keywords('Telkomsel'), true));
        $this->assertTrue(in_array('booster +fup', OperatorKeywords::keywords('Smartfren'), true));
        $this->assertTrue(in_array('nonstop', OperatorKeywords::keywords('Smartfren'), true));
    }

    // ==================== E-WALLET BRANDS ====================

    public function test_brand_ewallet_baru_terdeteksi(): void
    {
        $this->assertSame('Astrapay', EwalletBrands::brandOf('Topup Saldo Astrapay | Astrapay 10.000'));
        $this->assertSame('KasPro', EwalletBrands::brandOf('Top Up Saldo Kaspro | Kaspro 20.000'));
        $this->assertSame('KasPro', EwalletBrands::brandOf('Bebas Nominal Uang Elektronik | KasPro dan KAI Pay (Bebas Nominal)'));
        $this->assertSame('KAI Pay', EwalletBrands::brandOf('Bebas Nominal Uang Elektronik | Topup KAI Pay'));
        // Brand lama tetap berfungsi
        $this->assertSame('DANA', EwalletBrands::brandOf('Top Up Saldo DANA | DANA 25.000'));
    }

    public function test_warna_brand_baru_tersedia(): void
    {
        $this->assertSame('#8b5cf6', EwalletBrands::color('Astrapay'));
        $this->assertSame('#10b981', EwalletBrands::color('KasPro'));
        $this->assertSame('#3b82f6', EwalletBrands::color('BrandTidakDikenal'));
    }

    // ==================== BILL TYPES ====================

    public function test_bill_types_samsat_dan_bpr_koperasi(): void
    {
        $this->assertSame('Samsat & Pajak', BillTypes::detect('Samsat Kendaraan | Bayar E-Samsat Jawa Barat'));
        $this->assertSame('Finance', BillTypes::detect('Tagihan BPR dan Koperasi | Bayar Tagihan Koperasi Anugrah'));
        $this->assertSame('Finance', BillTypes::detect('Tagihan BPR dan Koperasi | Bayar Tagihan BPR Danagung Abadi'));
        // Jenis lain tidak berubah
        $this->assertSame('PLN', BillTypes::detect('Bayar PLN Bulanan | Bayar Tagihan Listrik'));
        $this->assertSame('PDAM', BillTypes::detect('PDAM Jawa Timur | Bayar PDAM Kabupaten Gresik'));
    }
}
