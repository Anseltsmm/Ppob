<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EwalletMenuTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
            'saldo' => 100000,
        ]);
    }

    private function seedProducts(): Category
    {
        $category = Category::create(['name' => 'E-Wallet', 'slug' => 'e-wallet-test']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'DANA 25.000',
            'code' => 'DN25',
            'type' => 'prepaid',
            'modal_price' => 24400,
            'sell_price' => 25500,
            'operator' => 'Top Up Saldo DANA',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'DANA 50.000',
            'code' => 'DN50',
            'type' => 'prepaid',
            'modal_price' => 49000,
            'sell_price' => 50500,
            'operator' => 'Top Up Saldo DANA',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'GoPay Topup (Bebas Nominal)',
            'code' => 'BBSGOP',
            'type' => 'opendenom',
            'modal_price' => 850,
            'admin_fee' => 2000,
            'min_nominal' => 10000,
            'max_nominal' => 1000000,
            'operator' => 'Bebas Nominal Uang Elektronik',
            'status' => true,
        ]);

        return $category;
    }

    public function test_halaman_ewallet_menampilkan_brand_yang_tersedia(): void
    {
        $this->seedProducts();

        $this->actingAs($this->customer())
            ->get(route('customer.ewallet.index'))
            ->assertOk()
            ->assertSee('Top Up E-Wallet')
            ->assertSee('DANA')
            ->assertSee('GoPay');
    }

    public function test_halaman_ewallet_kosong_saat_tidak_ada_produk(): void
    {
        $this->actingAs($this->customer())
            ->get(route('customer.ewallet.index'))
            ->assertOk()
            ->assertSee('Belum ada produk e-wallet tersedia');
    }

    public function test_endpoint_produk_memfilter_sesuai_brand(): void
    {
        $this->seedProducts();

        $this->actingAs($this->customer())
            ->getJson(route('customer.ewallet.products', ['brand' => 'DANA', 'number' => '081234567890']))
            ->assertOk()
            ->assertJsonCount(2, 'products')
            ->assertJsonPath('products.0.brand', 'DANA')
            ->assertJsonPath('products.1.brand', 'DANA');
    }

    public function test_endpoint_produk_menyertakan_field_open_denom(): void
    {
        $this->seedProducts();

        $response = $this->actingAs($this->customer())
            ->getJson(route('customer.ewallet.products', ['brand' => 'GoPay', 'number' => '081234567890']))
            ->assertOk();

        $product = $response->json('products.0');
        $this->assertSame('opendenom', $product['type']);
        $this->assertSame(2000, $product['admin_fee']);
        $this->assertSame(10000, $product['min_nominal']);
        $this->assertSame(1000000, $product['max_nominal']);
    }

    public function test_endpoint_produk_nomor_tidak_valid_ditolak(): void
    {
        $this->seedProducts();

        $this->actingAs($this->customer())
            ->getJson(route('customer.ewallet.products', ['brand' => 'DANA', 'number' => '123']))
            ->assertStatus(422);
    }
}
