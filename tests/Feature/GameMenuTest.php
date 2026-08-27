<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameMenuTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::firstOrCreate(
            ['email' => 'customer@test.dev'],
            [
                'name' => 'Customer',
                'password' => 'password',
                'saldo' => 100000,
            ]
        );
    }

    private function seedProducts(): void
    {
        $category = Category::create(['name' => 'Game', 'slug' => 'game-test']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'ML 86 Diamonds',
            'code' => 'ML86',
            'type' => 'prepaid',
            'sell_price' => 22000,
            'operator' => 'TPG Diamond Mobile Legends',
            'status' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Free Fire 100 Diamonds',
            'code' => 'FF100',
            'type' => 'prepaid',
            'sell_price' => 18000,
            'operator' => 'TPG Diamond Free Fire',
            'status' => true,
        ]);
    }

    public function test_halaman_game_menampilkan_brand_yang_tersedia(): void
    {
        $this->seedProducts();

        $this->actingAs($this->customer())
            ->get(route('customer.game.index'))
            ->assertOk()
            ->assertSee('Top Up Game')
            ->assertSee('Mobile Legends')
            ->assertSee('Free Fire');
    }

    public function test_halaman_game_kosong_saat_tidak_ada_produk(): void
    {
        $this->actingAs($this->customer())
            ->get(route('customer.game.index'))
            ->assertOk()
            ->assertSee('Belum ada produk game tersedia');
    }

    public function test_endpoint_produk_memfilter_sesuai_brand_game(): void
    {
        $this->seedProducts();

        $this->actingAs($this->customer())
            ->getJson(route('customer.game.products', ['brand' => 'Mobile Legends', 'user_id' => '81234567']))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'ML 86 Diamonds')
            ->assertJsonPath('products.0.brand', 'Mobile Legends');
    }

    public function test_endpoint_user_id_tidak_valid_ditolak(): void
    {
        $this->seedProducts();

        // Terlalu pendek
        $this->actingAs($this->customer())
            ->getJson(route('customer.game.products', ['brand' => 'Mobile Legends', 'user_id' => 'abc']))
            ->assertStatus(422);

        // Karakter tidak diizinkan
        $this->actingAs($this->customer())
            ->getJson(route('customer.game.products', ['brand' => 'Mobile Legends', 'user_id' => 'abc def$']))
            ->assertStatus(422);
    }
}
