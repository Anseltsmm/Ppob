<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OkeConnectCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $status = Order::STATUS_PENDING): Order
    {
        $user = User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
            'saldo' => 100000,
        ]);

        $category = Category::create(['name' => 'Pulsa', 'slug' => 'pulsa-'.uniqid()]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Telkomsel 10.000',
            'code' => 'T10',
            'type' => 'prepaid',
            'modal_price' => 10300,
            'sell_price' => 11000,
            'status' => true,
        ]);

        return Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'category_id' => $category->id,
            'order_code' => 'INV260825120000ABCD',
            'product_name' => $product->name,
            'destination' => '081234567890',
            'buy_price' => $product->modal_price,
            'sell_price' => $product->sell_price,
            'status' => $status,
        ]);
    }

    private function validToken(): string
    {
        return (string) Setting::where('key', 'okeconnect_callback_token')->value('value');
    }

    public function test_callback_tanpa_token_ditolak(): void
    {
        $order = $this->makeOrder();

        $this->get('/webhook/okeconnect?refid='.$order->order_code.'&message=SUKSES')
            ->assertStatus(401);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_callback_dengan_token_salah_ditolak(): void
    {
        $order = $this->makeOrder();

        $this->get('/webhook/okeconnect?refid='.$order->order_code.'&token=salah&message=SUKSES')
            ->assertStatus(401);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_callback_dengan_token_valid_mark_success(): void
    {
        $order = $this->makeOrder();

        $this->get('/webhook/okeconnect?refid='.$order->order_code.'&token='.$this->validToken().'&message=SUKSES')
            ->assertOk();

        $this->assertSame(Order::STATUS_SUCCESS, $order->fresh()->status);
    }

    public function test_callback_dengan_token_valid_refund_saat_gagal(): void
    {
        $order = $this->makeOrder();

        $this->get('/webhook/okeconnect?refid='.$order->order_code.'&token='.$this->validToken().'&message=GAGAL')
            ->assertOk();

        $order->refresh();
        $this->assertSame(Order::STATUS_FAILED, $order->status);
        // Saldo awal 100.000 + refund sell_price 11.000
        $this->assertEquals(111000, (float) $order->user->fresh()->saldo);
        $this->assertDatabaseHas('balance_histories', [
            'user_id' => $order->user_id,
            'type' => 'credit',
            'amount' => $order->sell_price,
        ]);
    }

    public function test_callback_refid_tidak_dikenal_mengembalikan_404(): void
    {
        $this->get('/webhook/okeconnect?refid=INV000000000000XXXX&token='.$this->validToken().'&message=SUKSES')
            ->assertStatus(404);
    }
}
