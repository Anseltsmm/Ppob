<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_debit_mengurangi_saldo_dan_mencatat_mutasi(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'debit@test.dev',
            'password' => 'password',
            'saldo' => 100000,
        ]);

        $result = $user->debit(25000, 'Beli pulsa');

        $this->assertTrue($result);
        $this->assertEquals(75000, (float) $user->fresh()->saldo);
        $this->assertDatabaseHas('balance_histories', [
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 25000,
        ]);
    }

    public function test_debit_gagal_saat_saldo_tidak_cukup(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'debit-gagal@test.dev',
            'password' => 'password',
            'saldo' => 10000,
        ]);

        $result = $user->debit(25000, 'Beli pulsa');

        $this->assertFalse($result);
        $this->assertEquals(10000, (float) $user->fresh()->saldo);
        $this->assertDatabaseMissing('balance_histories', ['user_id' => $user->id]);
    }

    public function test_credit_menambah_saldo_dan_mencatat_mutasi(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'credit@test.dev',
            'password' => 'password',
            'saldo' => 0,
        ]);

        $user->credit(50000, 'Topup saldo');

        $this->assertEquals(50000, (float) $user->fresh()->saldo);
        $this->assertDatabaseHas('balance_histories', [
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => 50000,
        ]);
    }

    public function test_mark_paid_hanya_mengkredit_saldo_sekali(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'markpaid@test.dev',
            'password' => 'password',
            'saldo' => 0,
        ]);

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'invoice' => 'INV2608250000AAAA',
            'amount' => 50000,
            'fee_customer' => 0,
            'total_amount' => 50000,
            'status' => 'UNPAID',
        ]);

        $deposit->markPaid(['reference' => 'REF123']);
        $deposit->markPaid(['reference' => 'REF123']); // dipanggil dua kali (callback + scheduler)

        $this->assertEquals('PAID', $deposit->fresh()->status);
        $this->assertEquals(50000, (float) $user->fresh()->saldo);
        $this->assertSame(1, $user->fresh()->balanceHistories()->count());
    }
}
