<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TagihanMenuTest extends TestCase
{
    use RefreshDatabase;

    private function user(int $saldo = 200000): User
    {
        return User::firstOrCreate(
            ['email' => 'customer@test.dev'],
            [
                'name' => 'Customer',
                'password' => 'password',
                'saldo' => $saldo,
            ]
        );
    }

    private function admin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@test.dev'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
            ]
        );
    }

    private function configureOkeConnect(): void
    {
        Setting::set('okeconnect_member_id', 'M123');
        Setting::set('okeconnect_pin', '1234');
        Setting::set('okeconnect_password', 'secret');
    }

    private function fakeCatalog(): void
    {
        Http::fake([
            'okeconnect.com/*' => Http::response(
                json_encode([
                    ['kode' => 'CPAM225', 'keterangan' => 'Cek PDAM Kabupaten Bangkalan', 'produk' => 'PDAM Jawa Timur', 'kategori' => 'AIR PDAM', 'harga' => '0', 'status' => '1'],
                    ['kode' => 'BPAM225', 'keterangan' => 'Bayar PDAM Kabupaten Bangkalan', 'produk' => 'PDAM Jawa Timur', 'kategori' => 'AIR PDAM', 'harga' => '-1350', 'status' => '1'],
                    ['kode' => 'CPLA', 'keterangan' => 'Cek Tagihan Listrik', 'produk' => 'Bayar PLN Bulanan', 'kategori' => 'TAGIHAN', 'harga' => '0', 'status' => '1'],
                    ['kode' => 'BPLA', 'keterangan' => 'Bayar Tagihan Listrik', 'produk' => 'Bayar PLN Bulanan', 'kategori' => 'TAGIHAN', 'harga' => '-1150', 'status' => '1'],
                    ['kode' => 'S5', 'keterangan' => 'Telkomsel 5.000', 'produk' => 'Telkomsel', 'kategori' => 'PULSA', 'harga' => '5230', 'status' => '1'],
                ]),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);
    }

    private function billProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Pascabayar'],
            ['slug' => 'pascabayar-'.uniqid()]
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Bayar PDAM Kabupaten Bangkalan',
            'code' => 'BPAM225',
            'inquiry_code' => 'CPAM225',
            'type' => 'prepaid',
            'modal_price' => 1350,
            'admin_fee' => 2350,
            'sell_price' => 0,
            'operator' => 'PDAM Jawa Timur',
            'status' => true,
        ], $overrides));
    }

    // ==================== IMPORT ====================

    public function test_import_produk_tagihan_ke_kategori_pascabayar(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx',
                'codes' => ['CPAM225', 'BPAM225', 'BPLA', 'S5'],
                'markup_type' => 'nominal',
                'markup_value' => 1000,
            ])->assertRedirect(route('admin.products.import'));

        // Produk bayar (B): kategori Pascabayar, inquiry_code C..., biaya admin = |harga| + markup
        $this->assertDatabaseHas('products', [
            'code' => 'BPAM225',
            'inquiry_code' => 'CPAM225',
            'modal_price' => 1350,
            'admin_fee' => 2350,
            'sell_price' => 0,
        ]);
        $this->assertSame('Pascabayar', Category::whereHas('products', fn ($q) => $q->where('code', 'BPAM225'))->first()->name);

        $this->assertDatabaseHas('products', [
            'code' => 'BPLA',
            'inquiry_code' => 'CPLA',
            'modal_price' => 1150,
            'admin_fee' => 2150,
            'sell_price' => 0,
        ]);

        // Produk inquiry (C, harga 0) dilewati — tidak di-import
        $this->assertDatabaseMissing('products', ['code' => 'CPAM225']);
        $this->assertDatabaseMissing('products', ['code' => 'CPLA']);

        // Produk biasa tetap normal
        $this->assertDatabaseHas('products', ['code' => 'S5', 'sell_price' => 6230]);
    }

    public function test_sync_harga_memperbarui_biaya_admin_produk_tagihan(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pascabayar', 'slug' => 'pascabayar-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Bayar PDAM Kabupaten Bangkalan',
            'code' => 'BPAM225',
            'inquiry_code' => 'CPAM225',
            'type' => 'prepaid',
            'modal_price' => 1000,
            'admin_fee' => 1500,
            'sell_price' => 0,
            'status' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.sync-prices'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx',
                'markup_type' => 'nominal',
                'markup_value' => 500,
            ])->assertRedirect(route('admin.products.import'));

        // modal 1350, admin_fee 1350 + 500 = 1850; sell_price tetap 0
        $this->assertDatabaseHas('products', ['code' => 'BPAM225', 'modal_price' => 1350, 'admin_fee' => 1850, 'sell_price' => 0]);
    }

    // ==================== HALAMAN ====================

    public function test_halaman_tagihan_menampilkan_jenis_dan_biller(): void
    {
        $this->billProduct();
        $this->billProduct([
            'name' => 'Bayar Tagihan Listrik',
            'code' => 'BPLA',
            'inquiry_code' => 'CPLA',
            'modal_price' => 1150,
            'admin_fee' => 2150,
            'operator' => 'Bayar PLN Bulanan',
        ]);

        $this->actingAs($this->user())
            ->get(route('customer.tagihan.index'))
            ->assertOk()
            ->assertSee('Tagihan & Pascabayar')
            ->assertSee('PDAM')
            ->assertSee('PLN')
            ->assertSee('Bayar PDAM Kabupaten Bangkalan')
            ->assertSee('Bayar Tagihan Listrik');
    }

    public function test_halaman_tagihan_kosong_saat_tidak_ada_produk(): void
    {
        $this->actingAs($this->user())
            ->get(route('customer.tagihan.index'))
            ->assertOk()
            ->assertSee('Belum ada produk tagihan tersedia.');
    }

    // ==================== INQUIRY ====================

    public function test_inquiry_mengembalikan_nominal_tagihan(): void
    {
        $this->configureOkeConnect();
        $product = $this->billProduct();

        Http::fake([
            'h2h.okeconnect.com/*' => Http::response(
                "SUKSES.\nT12345678\nIDPELANGGAN: 123456789012\nNAMA: BUDI SANTOSO\nTAGIHAN: 125000\nSaldo: 1.000.000",
                200
            ),
        ]);

        $this->actingAs($this->user())
            ->postJson(route('customer.tagihan.inquiry'), [
                'product_id' => $product->id,
                'destination' => '123456789012',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('nominal', 125000)
            ->assertJsonPath('admin_fee', 2350)
            ->assertJsonPath('total', 127350)
            ->assertJsonPath('customer_name', 'BUDI SANTOSO');

        // Inquiry tidak memotong saldo
        $this->assertEquals(200000, (float) $this->user()->fresh()->saldo);
    }

    public function test_inquiry_produk_bukan_tagihan_ditolak(): void
    {
        $this->configureOkeConnect();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel 5.000',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5230,
            'sell_price' => 6230,
            'status' => true,
        ]);

        $this->actingAs($this->user())
            ->postJson(route('customer.tagihan.inquiry'), [
                'product_id' => $product->id,
                'destination' => '081234567890',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Produk bukan layanan tagihan.');
    }

    public function test_inquiry_saat_okeconnect_belum_dikonfigurasi_ditolak(): void
    {
        $product = $this->billProduct();

        $this->actingAs($this->user())
            ->postJson(route('customer.tagihan.inquiry'), [
                'product_id' => $product->id,
                'destination' => '123456789012',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Layanan tagihan sedang dinonaktifkan sementara. Hubungi admin.');
    }

    public function test_inquiry_nominal_tidak_terdeteksi_mengembalikan_error(): void
    {
        $this->configureOkeConnect();
        $product = $this->billProduct();

        Http::fake([
            'h2h.okeconnect.com/*' => Http::response("SUKSES.\nT12345678", 200),
        ]);

        $this->actingAs($this->user())
            ->postJson(route('customer.tagihan.inquiry'), [
                'product_id' => $product->id,
                'destination' => '123456789012',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', "Nominal tagihan tidak terdeteksi dari respons. Detail: SUKSES.\nT12345678");
    }

    // ==================== ORDER ====================

    public function test_order_tagihan_memotong_saldo_selisih_nominal_dan_biaya_admin(): void
    {
        $this->configureOkeConnect();
        $user = $this->user();
        $product = $this->billProduct();

        Http::fake([
            'h2h.okeconnect.com/*' => Http::response("SUKSES.\nT999\nSN/Ref: 12345\nSaldo: 500.000", 200),
        ]);

        $this->actingAs($user)
            ->postJson(route('customer.orders.store', $product), [
                'destination' => '123456789012',
                'qty' => 125000,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        // Total = nominal 125.000 + admin 2.350 = 127.350
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'destination' => '123456789012',
            'qty' => 125000,
            'sell_price' => 127350,
            'buy_price' => 126350,
        ]);
        $this->assertEquals(200000 - 127350, (float) $user->fresh()->saldo);

        // Proses sync queue: transaksi sukses → order sukses, tidak refund
        $this->assertSame(Order::STATUS_SUCCESS, Order::first()->status);
    }

    public function test_order_tagihan_tanpa_nominal_ditolak(): void
    {
        $this->configureOkeConnect();
        $product = $this->billProduct();

        $this->actingAs($this->user())
            ->postJson(route('customer.orders.store', $product), [
                'destination' => '123456789012',
                // qty tidak dikirim
            ])
            ->assertStatus(422);
    }
}
