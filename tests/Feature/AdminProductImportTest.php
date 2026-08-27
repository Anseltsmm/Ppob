<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminProductImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.dev',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    private function fakeCatalog(): void
    {
        Http::fake([
            'okeconnect.com/*' => Http::response(
                json_encode([
                    ['kode' => 'S5', 'keterangan' => 'Telkomsel 5.000', 'produk' => 'Telkomsel', 'kategori' => 'PULSA', 'harga' => '5230', 'status' => '1'],
                    ['kode' => 'T5', 'keterangan' => 'Three 5.000', 'produk' => 'Three', 'kategori' => 'PULSA', 'harga' => '5148', 'status' => '1'],
                    ['kode' => 'BYU10', 'keterangan' => 'By U 10.000', 'produk' => 'By U', 'kategori' => 'PULSA', 'harga' => '10195', 'status' => '1'],
                    ['kode' => 'XLA5', 'keterangan' => 'XL - Axis 5.000', 'produk' => 'XL - Axis', 'kategori' => 'PULSA', 'harga' => '5852', 'status' => '0'],
                    ['kode' => 'PLN20', 'keterangan' => 'Token PLN 20.000', 'produk' => 'Token PLN Prabayar', 'kategori' => 'TOKEN PLN', 'harga' => '20400', 'status' => '1'],
                    ['kode' => 'BBSD', 'keterangan' => 'Dana Topup (Bebas Nominal)', 'produk' => 'Bebas Nominal Uang Elektronik', 'kategori' => 'NOMINAL BEBAS', 'harga' => '50', 'status' => '1'],
                ]),
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);
    }

    public function test_halaman_import_menampilkan_produk_dari_url(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->get(route('admin.products.import', ['url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa']))
            ->assertOk()
            ->assertSee('Telkomsel 5.000')
            ->assertSee('Three 5.000')
            ->assertSee('Konfirmasi Import')
            ->assertSee('confirm-import-btn');
    }

    public function test_filter_status_hanya_menampilkan_produk_aktif(): void
    {
        $this->fakeCatalog();

        // XLA5 berstatus '0' (nonaktif) di katalog → tidak tampil saat filter status=1
        $this->actingAs($this->admin())
            ->get(route('admin.products.import', [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'status' => '1',
            ]))
            ->assertOk()
            ->assertSee('Telkomsel 5.000')
            ->assertSee('Three 5.000')
            ->assertDontSee('XL - Axis 5.000');
    }

    public function test_filter_kategori_hanya_menampilkan_produk_kategori_tersebut(): void
    {
        $this->fakeCatalog();

        // Kategori TOKEN PLN di-map ke 'Token PLN' → hanya PLN20 yang tampil
        $this->actingAs($this->admin())
            ->get(route('admin.products.import', [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'category' => 'Token PLN',
            ]))
            ->assertOk()
            ->assertSee('Token PLN 20.000')
            ->assertDontSee('Telkomsel 5.000');
    }

    public function test_halaman_import_menampilkan_selisih_harga_produk_yang_sudah_ada(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel Lama',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5000,
            'sell_price' => 6000,
            'status' => true,
        ]);

        // S5 di katalog: modal 5230 vs lama 5000 → badge selisih +230
        $this->actingAs($this->admin())
            ->get(route('admin.products.import', ['url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa']))
            ->assertOk()
            ->assertSee('+230');
    }

    public function test_sync_harga_memperbarui_produk_yang_sudah_ada(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel 5.000',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5000,
            'sell_price' => 6000,
            'status' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.sync-prices'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'markup_type' => 'nominal',
                'markup_value' => 1000,
            ])
            ->assertRedirect(route('admin.products.import'));

        // S5: modal 5000 → 5230, jual 6000 → 6230; produk baru TIDAK dibuat
        $this->assertSame(1, Product::count());
        $this->assertDatabaseHas('products', ['code' => 'S5', 'modal_price' => 5230, 'sell_price' => 6230]);
    }

    public function test_sync_harga_menghitung_ulang_harga_jual(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Three 5.000',
            'code' => 'T5',
            'type' => 'prepaid',
            'modal_price' => 4000,
            'sell_price' => 5000,
            'status' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.sync-prices'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'markup_type' => 'percent',
                'markup_value' => 5,
            ])
            ->assertRedirect(route('admin.products.import'));

        // T5: modal 5148, jual 5148 + 5% = 5405,4 → 5405
        $this->assertDatabaseHas('products', ['code' => 'T5', 'modal_price' => 5148, 'sell_price' => 5405]);
    }

    public function test_import_produk_nominal_bebas_jadi_open_denom(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['BBSD'],
                'markup_type' => 'nominal',
                'markup_value' => 1000,
            ])
            ->assertRedirect(route('admin.products.import'));

        // Harga 50 = biaya admin OkeConnect; admin_fee = 50 + markup 1000
        $this->assertDatabaseHas('products', [
            'code' => 'BBSD',
            'type' => 'opendenom',
            'modal_price' => 50,
            'admin_fee' => 1050,
            'min_nominal' => 10000,
            'max_nominal' => 1000000,
        ]);
    }

    public function test_import_menyimpan_deskripsi_informatif(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5'],
                'markup_type' => 'none',
                'markup_value' => 0,
            ])
            ->assertRedirect(route('admin.products.import'));

        // Deskripsi informatif (bukan duplikat nama/keterangan)
        $product = Product::where('code', 'S5')->first();
        $this->assertNotSame($product->name, $product->description);
        $this->assertStringContainsString('Pulsa Telkomsel senilai Rp 5.000', $product->description);
    }

    public function test_import_tidak_menimpa_deskripsi_manual(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel 5.000',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5230,
            'sell_price' => 6230,
            'description' => 'Deskripsi hasil editan admin',
            'status' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5'],
                'markup_type' => 'none',
                'markup_value' => 0,
            ])
            ->assertRedirect(route('admin.products.import'));

        $this->assertSame('Deskripsi hasil editan admin', Product::where('code', 'S5')->first()->description);
    }

    public function test_import_menambahkan_produk_dengan_markup_nominal(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5', 'T5'],
                'markup_type' => 'nominal',
                'markup_value' => 1000,
            ])
            ->assertRedirect(route('admin.products.import'));

        $this->assertDatabaseHas('products', [
            'code' => 'S5',
            'name' => 'Telkomsel 5.000',
            'operator' => 'Telkomsel',
            'modal_price' => 5230,
            'sell_price' => 6230,
            'status' => true,
        ]);

        $this->assertSame('Pulsa', Category::whereHas('products', fn ($q) => $q->where('code', 'T5'))->first()->name);
    }

    public function test_import_memetakan_operator_dan_status(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['BYU10', 'XLA5'],
                'markup_type' => 'none',
                'markup_value' => 0,
            ])
            ->assertRedirect(route('admin.products.import'));

        // By U → Telkomsel (aktif), XL - Axis → XL (tidak aktif)
        $this->assertDatabaseHas('products', ['code' => 'BYU10', 'operator' => 'Telkomsel', 'status' => true]);
        $this->assertDatabaseHas('products', ['code' => 'XLA5', 'operator' => 'XL', 'status' => false]);
    }

    public function test_import_memperbarui_produk_yang_sudah_ada(): void
    {
        $this->fakeCatalog();

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-test']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel Lama',
            'code' => 'S5',
            'type' => 'prepaid',
            'modal_price' => 5000,
            'sell_price' => 6000,
            'status' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5'],
                'markup_type' => 'none',
                'markup_value' => 0,
            ])
            ->assertRedirect(route('admin.products.import'));

        // Tidak duplikat: kode sama diperbarui
        $this->assertSame(1, Product::where('code', 'S5')->count());
        $this->assertSame('Telkomsel 5.000', Product::where('code', 'S5')->first()->name);
    }

    public function test_import_dengan_markup_persen(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5'],
                'markup_type' => 'percent',
                'markup_value' => 5,
            ])
            ->assertRedirect(route('admin.products.import'));

        // 5230 + 5% = 5491,5 → dibulatkan 5492
        $this->assertDatabaseHas('products', ['code' => 'S5', 'modal_price' => 5230, 'sell_price' => 5492]);
    }

    public function test_import_markup_dipilih_tapi_nilai_kosong_ditolak(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => ['S5'],
                'markup_type' => 'nominal',
                // markup_value sengaja tidak dikirim
            ])
            ->assertSessionHasErrors('markup_value');
    }

    public function test_import_tanpa_kode_terpilih_ditolak(): void
    {
        $this->fakeCatalog();

        $this->actingAs($this->admin())
            ->post(route('admin.products.import-store'), [
                'url' => 'https://okeconnect.com/harga/json?id=xxx&produk=pulsa',
                'codes' => [],
                'markup_type' => 'nominal',
            ])
            ->assertSessionHasErrors('codes');
    }
}
