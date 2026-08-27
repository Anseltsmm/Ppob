<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\VoucherBrands;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CetakVoucherMenuTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['saldo' => 500_000]);
    }

    private function makeCategory(string $name): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => str()->slug($name).'-'.substr(md5($name), 0, 6),
            'sort' => 1,
        ]);
    }

    public function test_halaman_cetak_voucher_menampilkan_brand(): void
    {
        $cat = $this->makeCategory('Cetak Voucher');
        Product::create([
            'category_id' => $cat->id,
            'code' => 'IVA1',
            'name' => 'Act Voucher 1GB All 28 Hari',
            'operator' => 'Isat Cetak Voucher Unli',
            'modal_price' => 35_500,
            'sell_price' => 36_500,
            'type' => 'prepaid',
            'status' => true,
        ]);
        Product::create([
            'category_id' => $cat->id,
            'code' => 'TVA1',
            'name' => 'Act Voucher 2GB 28 Hari',
            'operator' => 'Tsel Cetak Voucher Jatim',
            'modal_price' => 20_000,
            'sell_price' => 21_000,
            'type' => 'prepaid',
            'status' => true,
        ]);

        $this->actingAs($this->customer())
            ->get(route('customer.cetak-voucher.index'))
            ->assertOk()
            ->assertSee('Indosat', false)
            ->assertSee('Telkomsel', false);
    }

    public function test_endpoint_produk_memfilter_per_brand(): void
    {
        $cat = $this->makeCategory('Cetak Voucher');
        Product::create([
            'category_id' => $cat->id,
            'code' => 'IVA1',
            'name' => 'Act Voucher 1GB All 28 Hari',
            'operator' => 'Isat Cetak Voucher Unli',
            'modal_price' => 35_500,
            'sell_price' => 36_500,
            'type' => 'prepaid',
            'status' => true,
        ]);
        Product::create([
            'category_id' => $cat->id,
            'code' => 'TVA1',
            'name' => 'Act Voucher 2GB 28 Hari',
            'operator' => 'Tsel Cetak Voucher Jatim',
            'modal_price' => 20_000,
            'sell_price' => 21_000,
            'type' => 'prepaid',
            'status' => true,
        ]);

        $this->actingAs($this->customer())
            ->getJson(route('customer.cetak-voucher.products', ['brand' => 'Indosat']))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Act Voucher 1GB All 28 Hari');
    }

    public function test_produk_harga_0_tidak_muncul_di_menu(): void
    {
        $cat = $this->makeCategory('Cetak Voucher');
        // Produk inquiry (Cek Status Voucher) harga 0 — harus tidak tampil
        Product::create([
            'category_id' => $cat->id,
            'code' => 'CEKTSEL',
            'name' => 'Cek Status Vcr Telkomsel',
            'operator' => 'Cek Status Voucher',
            'modal_price' => 0,
            'sell_price' => 0,
            'type' => 'prepaid',
            'status' => true,
        ]);

        $this->actingAs($this->customer())
            ->getJson(route('customer.cetak-voucher.products', ['brand' => 'Telkomsel']))
            ->assertOk()
            ->assertJsonCount(0, 'products');
    }

    public function test_import_mapping_kategori_cetak_voucher_dan_pulsa_transfer(): void
    {
        // Simulasi fetch: kategori mentah CETAK VOUCHER & PULSA TRANSFER
        $svc = $this->getMockBuilder(\App\Services\OkeConnectCatalogService::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $svc->method('fetch')->willReturn([
            [
                'code' => 'IVA1', 'name' => 'Act Voucher 1GB', 'operator' => 'Isat Cetak Voucher Unli',
                'category' => 'Cetak Voucher', 'modal_price' => 35_500, 'active' => true,
                'open_denom' => false, 'inquiry_code' => null,
            ],
            [
                'code' => 'CEKTSEL', 'name' => 'Cek Status Vcr Telkomsel', 'operator' => 'Cek Status Voucher',
                'category' => 'Cetak Voucher', 'modal_price' => 0, 'active' => true,
                'open_denom' => false, 'inquiry_code' => null, 'skip_import' => true,
            ],
            [
                'code' => 'BYUDRCT15', 'name' => 'BYU 15.000', 'operator' => 'PULSA BYU DIRECT',
                'category' => 'Pulsa Transfer', 'modal_price' => 14_875, 'active' => false,
                'open_denom' => false, 'inquiry_code' => null,
            ],
        ]);

        $result = $svc->import(['IVA1', 'CEKTSEL', 'BYUDRCT15'], 'nominal', 1000);

        $this->assertSame(['created' => 2, 'updated' => 0, 'skipped' => 1], $result);
        $this->assertDatabaseHas('categories', ['name' => 'Cetak Voucher']);
        $this->assertDatabaseHas('categories', ['name' => 'Pulsa Transfer']);
        $this->assertDatabaseHas('products', ['code' => 'IVA1', 'sell_price' => 36_500]);
        $this->assertDatabaseMissing('products', ['code' => 'CEKTSEL']);
    }

    public function test_halaman_pulsa_transfer(): void
    {
        $this->actingAs($this->customer())
            ->get(route('customer.pulsa-transfer.index'))
            ->assertOk()
            ->assertSee('Pulsa Transfer', false);
    }

    public function test_endpoint_pulsa_transfer_hanya_produk_aktif(): void
    {
        $cat = $this->makeCategory('Pulsa Transfer');
        Product::create([
            'category_id' => $cat->id,
            'code' => 'BYUDRCT15',
            'name' => 'BYU 15.000',
            'operator' => 'PULSA BYU DIRECT',
            'modal_price' => 14_875,
            'sell_price' => 15_875,
            'type' => 'prepaid',
            'status' => true,
        ]);

        $this->actingAs($this->customer())
            ->getJson(route('customer.pulsa-transfer.products', ['number' => '089512345678']))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'BYU 15.000');
    }

    public function test_brand_deteksi(): void
    {
        $this->assertSame('Telkomsel', VoucherBrands::brandOf('Tsel Cetak Voucher Jatim'));
        $this->assertSame('Indosat', VoucherBrands::brandOf('Isat Cetak Voucher Unli'));
        $this->assertSame('Three', VoucherBrands::brandOf('Tri Cetak Vcr Happy West Java'));
        $this->assertSame('AXIS', VoucherBrands::brandOf('Cetak Voucher Aigo Axis'));
        $this->assertSame('By.U', VoucherBrands::brandOf('ByU Cetak Voucher Harian'));
        $this->assertSame('Smartfren', VoucherBrands::brandOf('Smart Cetak Voucher Nonstop'));
        $this->assertNull(VoucherBrands::brandOf('Cek Status Voucher'));
    }

    public function test_dashboard_hanya_kategori_dengan_produk_aktif(): void
    {
        // Kategori dengan produk aktif → muncul
        $active = $this->makeCategory('Cetak Voucher');
        Product::create([
            'category_id' => $active->id,
            'code' => 'IVA1',
            'name' => 'Act Voucher 1GB',
            'operator' => 'Isat Cetak Voucher Unli',
            'modal_price' => 35_500,
            'sell_price' => 36_500,
            'type' => 'prepaid',
            'status' => true,
        ]);

        // Kategori tanpa produk aktif → tidak muncul (hindari tile kosong)
        $empty = $this->makeCategory('Pulsa Transfer');
        Product::create([
            'category_id' => $empty->id,
            'code' => 'BYUDRCT15',
            'name' => 'BYU 15.000',
            'operator' => 'PULSA BYU DIRECT',
            'modal_price' => 14_875,
            'sell_price' => 15_875,
            'type' => 'prepaid',
            'status' => false,
        ]);

        $this->actingAs($this->customer())
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Cetak Voucher', false)
            ->assertDontSee('Pulsa Transfer', false);
    }
}
