<?php

namespace App\Support;

/**
 * Deteksi jenis layanan pembayaran (PLN, PDAM, PBB, TV & Internet, dll.)
 * dari nama/operator produk pascabayar di katalog OkeConnect.
 */
class BillTypes
{
    /**
     * Urutan deteksi penting: kecocokan pertama yang menang.
     */
    private const TYPES = [
        'PLN' => ['pln', 'listrik'],
        'PDAM' => ['pdam'],
        'PBB' => ['pbb'],
        'Samsat & Pajak' => ['samsat', 'pajak'],
        'Gas & Energi' => ['gas', 'pgn', 'pertagas', 'elpiji'],
        'BPJS & Asuransi' => ['bpjs', 'asuransi', 'kesehatan', 'ketenagakerjaan'],
        'Finance' => ['cicilan', 'kredit', 'kpr', 'leasing', 'pinjaman', 'home credit', 'finance', 'kartu kredit', 'bpr', 'koperasi'],
        'TV & Internet' => ['televisi', 'indihome', 'speedy', 'internet', 'first media', 'my republic', 'biznet', 'iconnet', 'commet', 'xl home', 'fiber', 'cbn', 'wifi', 'parabola', 'vision', 'tv', 'media', 'topas', 'nusantara', 'garmedia', 'jawara', 'k-vision', 'matrix', 'skynindo'],
        'Telepon' => ['telepon', 'telkom'],
    ];

    public static function detect(string $text): string
    {
        $text = strtolower($text);

        foreach (self::TYPES as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $type;
                }
            }
        }

        return 'Lainnya';
    }

    public static function icon(string $type): string
    {
        return match ($type) {
            'PLN' => 'bi-lightning-charge',
            'PDAM' => 'bi-droplet',
            'PBB' => 'bi-building',
            'Samsat & Pajak' => 'bi-car-front',
            'Gas & Energi' => 'bi-fire',
            'BPJS & Asuransi' => 'bi-shield-plus',
            'Finance' => 'bi-credit-card',
            'TV & Internet' => 'bi-tv',
            'Telepon' => 'bi-telephone',
            default => 'bi-receipt',
        };
    }

    public static function color(string $type): string
    {
        return match ($type) {
            'PLN' => '#f59e0b',
            'PDAM' => '#0ea5e9',
            'PBB' => '#8b5cf6',
            'Samsat & Pajak' => '#f97316',
            'Gas & Energi' => '#ef4444',
            'BPJS & Asuransi' => '#10b981',
            'Finance' => '#ec4899',
            'TV & Internet' => '#6366f1',
            'Telepon' => '#14b8a6',
            default => '#64748b',
        };
    }
}
