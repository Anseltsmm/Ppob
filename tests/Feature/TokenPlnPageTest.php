<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenPlnPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_token_pln_menampilkan_info_penting_dan_tema(): void
    {
        $user = User::create([
            'name' => 'Customer',
            'email' => 'customer@test.dev',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get(route('customer.token-pln.index'))
            ->assertOk()
            ->assertSee('Token PLN')
            ->assertSee('Nomor meter PLN')
            ->assertSee('Pastikan nomor meter (ID pelanggan) sudah benar');
    }
}
