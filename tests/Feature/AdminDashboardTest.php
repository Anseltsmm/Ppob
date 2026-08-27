<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_menampilkan_statistik_dan_saldo_okeconnect(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.dev',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Saldo OkeConnect')
            ->assertSee('Order Pending')
            ->assertSee('Tren Order & Profit', false);
    }

    public function test_dashboard_ditolak_untuk_customer(): void
    {
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
