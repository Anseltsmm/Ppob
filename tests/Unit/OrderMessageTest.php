<?php

namespace Tests\Unit;

use App\Models\Order;
use PHPUnit\Framework\TestCase;

class OrderMessageTest extends TestCase
{
    private function order(?string $message): Order
    {
        $order = new Order;
        $order->message = $message;

        return $order;
    }

    public function test_menghapus_saldo_dari_response_sukses(): void
    {
        $order = $this->order("SUKSES.\nT999\nSN/Ref: 12345\nSaldo: 500.000");

        $this->assertSame("SUKSES.\nT999\nSN/Ref: 12345", $order->customerMessage());
    }

    public function test_menghapus_saldo_dari_response_gagal(): void
    {
        $order = $this->order('GAGAL. Produk tidak tersedia. Saldo: 500.000');

        $this->assertSame('GAGAL. Produk tidak tersedia.', $order->customerMessage());
    }

    public function test_menghapus_saldo_anda(): void
    {
        $order = $this->order("SALDO ANDA: 10.000.000");

        $this->assertNull($order->customerMessage());
    }

    public function test_message_tanpa_saldo_tidak_berubah(): void
    {
        $order = $this->order("SUKSES.\nT12345678");

        $this->assertSame("SUKSES.\nT12345678", $order->customerMessage());
    }

    public function test_tanpa_message_mengembalikan_null(): void
    {
        $this->assertNull($this->order(null)->customerMessage());
    }
}
