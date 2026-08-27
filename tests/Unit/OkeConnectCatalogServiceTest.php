<?php

namespace Tests\Unit;

use App\Services\OkeConnectCatalogService;
use PHPUnit\Framework\TestCase;

class OkeConnectCatalogServiceTest extends TestCase
{
    private OkeConnectCatalogService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OkeConnectCatalogService;
    }

    public function test_markup_nominal_menambah_harga_modal(): void
    {
        $this->assertSame(6230, (int) $this->service->sellPrice(5230, 'nominal', 1000));
    }

    public function test_markup_persen_dihitung_dari_harga_modal(): void
    {
        $this->assertSame(5753, (int) $this->service->sellPrice(5230, 'percent', 10));
        $this->assertSame(5492, (int) $this->service->sellPrice(5230, 'percent', 5));
    }

    public function test_markup_persen_fraksional_dibulatkan(): void
    {
        // 5230 + 12,5% = 5883,75 → 5884
        $this->assertSame(5884, (int) $this->service->sellPrice(5230, 'percent', 12.5));
    }

    public function test_tanpa_markup_harga_jual_sama_dengan_modal(): void
    {
        $this->assertSame(5230, (int) $this->service->sellPrice(5230, 'none', 0));
        $this->assertSame(5230, (int) $this->service->sellPrice(5230, 'nominal', 0));
    }
}
