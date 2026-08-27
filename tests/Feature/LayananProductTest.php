<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayananProductTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
        ]);
    }

    private function seedProducts(): void
    {
        $data = Category::create(['name' => 'Paket Data', 'slug' => 'paket-data-test']);
        $pulsa = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);

        Product::create([
            'category_id' => $data->id,
            'name' => 'Telkomsel 1 GB',
            'code' => 'D1',
            'type' => 'prepaid',
            'modal_price' => 15000,
            'sell_price' => 16000,
            'operator' => 'Data Tsel Jatim - Madura',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $data->id,
            'name' => 'Indosat Freedom 1 GB',
            'code' => 'SD1',
            'type' => 'prepaid',
            'modal_price' => 14500,
            'sell_price' => 15500,
            'operator' => 'Indosat Freedom',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $pulsa->id,
            'name' => 'Telkomsel 5.000',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5230,
            'sell_price' => 6500,
            'operator' => 'Telkomsel',
            'status' => true,
        ]);
    }

    public function test_voucher_data_mencocokkan_operator_by_keyword(): void
    {
        $this->seedProducts();

        // Operator 'Telkomsel' → cocok dengan operator "Data Tsel Jatim - Madura"
        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081234567890',
                'operator' => 'Telkomsel',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Telkomsel 1 GB');
    }

    public function test_pulsa_masih_mencocokkan_operator_persis(): void
    {
        $this->seedProducts();

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pulsa',
                'number' => '081234567890',
                'operator' => 'Telkomsel',
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'products'); // Telkomsel 1 GB (tsel) + Telkomsel 5.000
    }

    public function test_operator_tidak_dikenal_ditolak(): void
    {
        $this->seedProducts();

        // Operator di luar daftar deteksi prefix ditolak validasi
        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081234567890',
                'operator' => 'OperatorFiktif',
            ]))
            ->assertStatus(422);
    }

    public function test_voucher_data_mencocokkan_produk_freedoom_indosat(): void
    {
        $this->seedProducts();

        Product::create([
            'category_id' => Category::where('name', 'Paket Data')->first()->id,
            'name' => '15GB All + 5GB Malam 28 Hari',
            'code' => 'IDN15',
            'type' => 'prepaid',
            'modal_price' => 39000,
            'sell_price' => 40000,
            'operator' => 'Freedoom Combo Non Attack',
            'status' => true,
        ]);

        // Operator Indosat → cocok dengan produk ber-operator "Freedoom Combo Non Attack"
        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081412345678',
                'operator' => 'Indosat',
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'products') // Indosat Freedom 1 GB (seed) + Freedoom
            ->assertJsonFragment(['name' => '15GB All + 5GB Malam 28 Hari']);
    }

    public function test_voucher_data_mencocokkan_produk_bronet_axis(): void
    {
        $this->seedProducts();

        Product::create([
            'category_id' => Category::where('name', 'Paket Data')->first()->id,
            'name' => 'Bronet 2GB All 24 Jam 60 Hari',
            'code' => 'AXD2',
            'type' => 'prepaid',
            'modal_price' => 28000,
            'sell_price' => 30000,
            'operator' => 'Bronet Isi Ulang 60 Hari',
            'status' => true,
        ]);

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '083112345678',
                'operator' => 'AXIS',
            ]))
            ->assertOk()
            ->assertJsonPath('products.0.name', 'Bronet 2GB All 24 Jam 60 Hari');
    }

    public function test_pulsa_scope_mengklasifikasikan_jenis_produk_dan_mengecualikan_cekbayar(): void
    {
        $pulsa = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-klasifikasi']);
        $data = Category::create(['name' => 'Paket Data', 'slug' => 'paket-data-klasifikasi']);

        Product::create([
            'category_id' => $pulsa->id,
            'name' => 'Telkomsel 5.000',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5230,
            'sell_price' => 6230,
            'operator' => 'Telkomsel',
            'status' => true,
        ]);
        Product::create([
            'category_id' => $pulsa->id,
            'name' => '500 SMS + 100 SMS Opt lain 30 Hari',
            'code' => 'TSMS1',
            'type' => 'prepaid',
            'modal_price' => 4000,
            'sell_price' => 5000,
            'operator' => 'Tsel Telepon New',
            'status' => true,
        ]);
        Product::create([
            'category_id' => $pulsa->id,
            'name' => 'Masa Aktif Telkomsel + 30 Hari',
            'code' => 'MAST30',
            'type' => 'prepaid',
            'modal_price' => 14468,
            'sell_price' => 15468,
            'operator' => '+Masa Aktif',
            'status' => true,
        ]);
        // Pola Cek/Bayar (harga 0) harus dikecualikan — bukan produk harga tetap
        Product::create([
            'category_id' => $pulsa->id,
            'name' => 'Bayar 6250 Menit Tsel + 250 Menit All 30 Hari',
            'code' => 'BYRT5',
            'type' => 'prepaid',
            'modal_price' => 0,
            'sell_price' => 1000,
            'operator' => 'Tsel Telepon Digipos',
            'status' => true,
        ]);
        Product::create([
            'category_id' => $data->id,
            'name' => 'Telkomsel 1 GB',
            'code' => 'D1',
            'type' => 'prepaid',
            'modal_price' => 15000,
            'sell_price' => 16000,
            'operator' => 'Data Tsel Jatim - Madura',
            'status' => true,
        ]);

        $response = $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pulsa',
                'number' => '081234567890',
                'operator' => 'Telkomsel',
            ]))
            ->assertOk()
            ->assertJsonCount(4, 'products');

        $products = collect($response->json('products'));

        // Klasifikasi per jenis (respons tidak menyertakan code, cocokkan via nama)
        $this->assertSame('Pulsa', $products->firstWhere('name', 'Telkomsel 5.000')['kind']);
        $this->assertSame('SMS & Telepon', $products->firstWhere('name', '500 SMS + 100 SMS Opt lain 30 Hari')['kind']);
        $this->assertSame('Masa Aktif', $products->firstWhere('name', 'Masa Aktif Telkomsel + 30 Hari')['kind']);
        $this->assertSame('Paket Data', $products->firstWhere('name', 'Telkomsel 1 GB')['kind']);

        // Denom khusus per jenis
        $this->assertSame('500 SMS', $products->firstWhere('name', '500 SMS + 100 SMS Opt lain 30 Hari')['denom']);
        $this->assertSame('30 Hari', $products->firstWhere('name', 'Masa Aktif Telkomsel + 30 Hari')['denom']);

        // Produk Cek/Bayar (modal 0) tidak ikut tampil
        $this->assertFalse($products->contains(fn ($p) => $p['name'] === 'Bayar 6250 Menit Tsel + 250 Menit All 30 Hari'));
    }

    public function test_halaman_pulsa_merender_tab_sms_dan_masa_aktif(): void
    {
        $this->actingAs($this->user())
            ->get(route('customer.pulsa.index'))
            ->assertOk()
            ->assertSee('Pulsa Reguler')
            ->assertSee('SMS & Telepon', false)
            ->assertSee('Masa Aktif')
            ->assertSee('Paket Data');
    }

    public function test_token_pln_mengirim_sub_brand_operator(): void
    {
        $pln = Category::create(['name' => 'Token PLN', 'slug' => 'token-pln-subbrand']);

        // Dua tipe produk berbeda di kategori Token PLN
        Product::create([
            'category_id' => $pln->id, 'code' => 'PLN20',
            'name' => 'Token PLN 20.000', 'operator' => 'Token Listrik',
            'modal_price' => 20400, 'sell_price' => 21000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $pln->id, 'code' => 'PLN50',
            'name' => 'Token PLN 50.000', 'operator' => 'Token Listrik',
            'modal_price' => 50400, 'sell_price' => 51500, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $pln->id, 'code' => 'PLNNON100',
            'name' => 'PLN Non-Tagihan 100.000', 'operator' => 'PLN Non-Tagihan',
            'modal_price' => 100000, 'sell_price' => 101000, 'type' => 'prepaid', 'status' => true,
        ]);

        $response = $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pln',
                'number' => '12345678901',
            ]))
            ->assertOk()
            ->assertJsonCount(3, 'products')
            ->assertJsonPath('sub_brands', ['Token Listrik', 'PLN Non-Tagihan']);

        $products = collect($response->json('products'));

        $this->assertSame('Token Listrik', $products->firstWhere('name', 'Token PLN 20.000')['sub_brand']);
        $this->assertSame('PLN Non-Tagihan', $products->firstWhere('name', 'PLN Non-Tagihan 100.000')['sub_brand']);
    }

    public function test_token_pln_mengecualikan_produk_h2h(): void
    {
        $token = Category::create(['name' => 'Token PLN', 'slug' => 'token-pln-test']);

        Product::create([
            'category_id' => $token->id,
            'name' => 'Token PLN 20.000',
            'code' => 'PLN20',
            'type' => 'prepaid',
            'modal_price' => 20400,
            'sell_price' => 21000,
            'operator' => 'Token PLN Prabayar',
            'status' => true,
        ]);
        // Produk H2H harus disembunyikan
        Product::create([
            'category_id' => $token->id,
            'name' => 'H2H Token PLN 100.000',
            'code' => 'HP100',
            'type' => 'prepaid',
            'modal_price' => 100000,
            'sell_price' => 101000,
            'operator' => 'H2H Token PLN Terbaik',
            'status' => true,
        ]);
        // Produk non-token tapi bukan H2H — tetap tampil (bisa difilter via sub-kategori)
        Product::create([
            'category_id' => $token->id,
            'name' => 'Tap Cash BNI 20.000',
            'code' => 'BNI20',
            'type' => 'prepaid',
            'modal_price' => 21000,
            'sell_price' => 22000,
            'operator' => 'Top Up Saldo Tapcash BNI',
            'status' => true,
        ]);

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pln',
                'number' => '12345678901',
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'products');
    }

    public function test_endpoint_voucher_mengirim_daftar_sub_brand(): void
    {
        $cat = Category::where('name', 'Paket Data')->first() ?? Category::create([
            'name' => 'Paket Data', 'slug' => 'paket-data-test', 'sort' => 1,
        ]);

        Product::create([
            'category_id' => $cat->id, 'code' => 'XLBP1',
            'name' => 'Bebas Puas 1GB 1 Hari', 'operator' => 'XL Bebas Puas 5K',
            'modal_price' => 5000, 'sell_price' => 6000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $cat->id, 'code' => 'XLBP2',
            'name' => 'Bebas Puas 2GB 1 Hari', 'operator' => 'XL Bebas Puas 3K',
            'modal_price' => 7000, 'sell_price' => 8000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $cat->id, 'code' => 'XLPU1',
            'name' => 'Pure Data 1GB 30 Hari', 'operator' => 'XL Pure Data',
            'modal_price' => 9000, 'sell_price' => 10000, 'type' => 'prepaid', 'status' => true,
        ]);

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081712345678',
                'operator' => 'XL',
            ]))
            ->assertOk()
            ->assertJsonCount(3, 'products')
            ->assertJsonPath('sub_brands', ['Bebas Puas', 'Pure Data'])
            ->assertJsonPath('products.0.sub_brand', 'Bebas Puas');
    }

    public function test_endpoint_voucher_filter_sub_brand(): void
    {
        $cat = Category::where('name', 'Paket Data')->first() ?? Category::create([
            'name' => 'Paket Data', 'slug' => 'paket-data-test', 'sort' => 1,
        ]);

        Product::create([
            'category_id' => $cat->id, 'code' => 'XLBP1',
            'name' => 'Bebas Puas 1GB 1 Hari', 'operator' => 'XL Bebas Puas 5K',
            'modal_price' => 5000, 'sell_price' => 6000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $cat->id, 'code' => 'XLPU1',
            'name' => 'Pure Data 1GB 30 Hari', 'operator' => 'XL Pure Data',
            'modal_price' => 9000, 'sell_price' => 10000, 'type' => 'prepaid', 'status' => true,
        ]);

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081712345678',
                'operator' => 'XL',
                'sub_brand' => 'Bebas Puas',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Bebas Puas 1GB 1 Hari');
    }

    public function test_pulsa_dan_voucher_data_terpisah_berdasarkan_kategori(): void
    {
        $pulsa = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-pisah']);
        $data = Category::create(['name' => 'Paket Data', 'slug' => 'paket-data-pisah']);

        Product::create([
            'category_id' => $pulsa->id, 'code' => 'T5',
            'name' => 'Telkomsel 5.000', 'operator' => 'Telkomsel',
            'modal_price' => 5900, 'sell_price' => 6500, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $data->id, 'code' => 'D1',
            'name' => 'Telkomsel 1 GB', 'operator' => 'Telkomsel',
            'modal_price' => 15100, 'sell_price' => 16000, 'type' => 'prepaid', 'status' => true,
        ]);

        $user = $this->user();

        // Voucher Data (scope 'voucher') → HANYA produk kategori Paket Data,
        // produk pulsa tidak ikut tampil
        $voucher = $this->actingAs($user)
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '081234567890',
                'operator' => 'Telkomsel',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->json('products');

        $this->assertSame('Telkomsel 1 GB', $voucher[0]['name']);
        $this->assertSame('Paket Data', $voucher[0]['kind']);

        // Pulsa (scope 'pulsa') → kedua kategori tampil, tapi kind terpisah:
        // produk pulsa → 'Pulsa', produk paket data → 'Paket Data'
        $response = $this->actingAs($user)
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pulsa',
                'number' => '081234567890',
                'operator' => 'Telkomsel',
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'products');

        $products = collect($response->json('products'));

        $this->assertSame('Pulsa', $products->firstWhere('name', 'Telkomsel 5.000')['kind']);
        $this->assertSame('Paket Data', $products->firstWhere('name', 'Telkomsel 1 GB')['kind']);
    }

    public function test_pulsa_scope_mengirim_sub_brand_hanya_untuk_paket_data(): void
    {
        $pulsa = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-subbrand']);
        $data = Category::create(['name' => 'Paket Data', 'slug' => 'paket-data-subbrand']);

        // Pulsa reguler — tidak punya sub-brand
        Product::create([
            'category_id' => $pulsa->id, 'code' => 'XL10',
            'name' => 'XL 10.000', 'operator' => 'XL',
            'modal_price' => 10000, 'sell_price' => 11000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $data->id, 'code' => 'XLBP1',
            'name' => 'Bebas Puas 1GB 1 Hari', 'operator' => 'XL Bebas Puas 5K',
            'modal_price' => 5000, 'sell_price' => 6000, 'type' => 'prepaid', 'status' => true,
        ]);
        Product::create([
            'category_id' => $data->id, 'code' => 'XLPU1',
            'name' => 'Pure Data 1GB 30 Hari', 'operator' => 'XL Pure Data',
            'modal_price' => 9000, 'sell_price' => 10000, 'type' => 'prepaid', 'status' => true,
        ]);

        $response = $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'pulsa',
                'number' => '081712345678',
                'operator' => 'XL',
            ]))
            ->assertOk()
            ->assertJsonCount(3, 'products')
            ->assertJsonPath('sub_brands', ['Bebas Puas', 'Pure Data']);

        $products = collect($response->json('products'));

        // Produk paket data punya sub-brand; pulsa reguler tidak
        $this->assertSame('Bebas Puas', $products->firstWhere('name', 'Bebas Puas 1GB 1 Hari')['sub_brand']);
        $this->assertSame('Pure Data', $products->firstWhere('name', 'Pure Data 1GB 30 Hari')['sub_brand']);
        $this->assertNull($products->firstWhere('name', 'XL 10.000')['sub_brand']);
    }

    public function test_produk_inquiry_tidak_muncul_di_endpoint(): void
    {
        $cat = Category::where('name', 'Paket Data')->first() ?? Category::create([
            'name' => 'Paket Data', 'slug' => 'paket-data-test', 'sort' => 1,
        ]);

        Product::create([
            'category_id' => $cat->id, 'code' => 'BYRMAX',
            'name' => 'Bayar Paket Tri Cuan Max', 'operator' => 'Tri Cuan Max Direct',
            'modal_price' => 999, 'sell_price' => 1999, 'type' => 'prepaid', 'status' => true,
        ]);

        $this->actingAs($this->user())
            ->getJson(route('customer.pulsa.products', [
                'scope' => 'voucher',
                'number' => '089512345678',
                'operator' => 'Three',
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'products')
            ->assertJsonPath('sub_brands', []);
    }

    public function test_import_melewati_produk_inquiry_cek_bayar(): void
    {
        $svc = $this->getMockBuilder(\App\Services\OkeConnectCatalogService::class)
            ->onlyMethods(['fetch'])
            ->getMock();

        $svc->method('fetch')->willReturn([
            [
                'code' => 'XLBPP', 'name' => 'Bebas Puas 5GB 1 Hari', 'operator' => 'XL Bebas Puas 5K',
                'category' => 'Paket Data', 'modal_price' => 10_000, 'active' => true,
                'open_denom' => false, 'inquiry_code' => null,
            ],
            [
                'code' => 'BYRXLCUAN', 'name' => 'Bayar Paket XL Cuanku', 'operator' => 'XL Data Cuanku Spesial',
                'category' => 'Paket Data', 'modal_price' => 999, 'active' => true,
                'open_denom' => false, 'inquiry_code' => null, 'skip_import' => true,
            ],
            [
                'code' => 'CEKXLCUAN', 'name' => 'Cek Harga XL Cuanku', 'operator' => 'XL Data Cuanku Spesial',
                'category' => 'Paket Data', 'modal_price' => 999, 'active' => true,
                'open_denom' => false, 'inquiry_code' => null, 'skip_import' => true,
            ],
        ]);

        $result = $svc->import(['XLBPP', 'BYRXLCUAN', 'CEKXLCUAN'], 'nominal', 1000);

        $this->assertSame(['created' => 1, 'updated' => 0, 'skipped' => 2], $result);
        $this->assertDatabaseHas('products', ['code' => 'XLBPP']);
        $this->assertDatabaseMissing('products', ['code' => 'BYRXLCUAN']);
        $this->assertDatabaseMissing('products', ['code' => 'CEKXLCUAN']);
    }
}
