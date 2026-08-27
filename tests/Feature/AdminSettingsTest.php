<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
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

    public function test_halaman_pengaturan_menampilkan_info_callback_dan_ip_vps(): void
    {
        Http::fake([
            'api.ipify.org/*' => Http::response('47.129.192.207'),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Informasi Callback')
            ->assertSee('IP Publik VPS')
            ->assertSee('47.129.192.207')
            ->assertSee('Tripay Callback URL')
            ->assertSee('OkeConnect Callback URL')
            ->assertSee('Regenerate Token')
            ->assertSee('copy-btn', false);
    }

    public function test_submit_okeconnect_tanpa_tripay_mode_tetap_tersimpan(): void
    {
        $admin = $this->admin();

        // Simulasi submit form OkeConnect — hanya field OkeConnect,
        // TANPA tripay_mode (karena field itu di form terpisah)
        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'okeconnect_base_url' => 'https://h2h.okeconnect.com',
                'okeconnect_member_id' => 'OK123',
                'okeconnect_pin' => '9999',
                'okeconnect_password' => 'secret',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Nilai tersimpan
        $this->assertSame('OK123', Setting::get('okeconnect_member_id'));
        $this->assertSame('9999', Setting::get('okeconnect_pin'));
        $this->assertSame('secret', Setting::get('okeconnect_password'));
    }

    public function test_submit_tripay_dengan_tripay_mode_tersimpan(): void
    {
        $admin = $this->admin();

        // Form Tripay — termasuk tripay_mode
        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'tripay_mode' => 'sandbox',
                'tripay_api_key' => 'test-key',
                'tripay_merchant_code' => 'T0001',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('sandbox', Setting::get('tripay_mode'));
        $this->assertSame('test-key', Setting::get('tripay_api_key'));
    }

    public function test_halaman_pengaturan_ditolak_untuk_customer(): void
    {
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }
}
