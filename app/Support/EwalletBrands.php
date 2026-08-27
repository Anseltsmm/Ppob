<?php

namespace App\Support;

/**
 * Deteksi brand e-wallet dari nama produk / operator OkeConnect.
 *
 * Operator dari OkeConnect berupa teks seperti "Top Up Saldo DANA",
 * "Top Up Saldo Gopay Promo", "Bebas Nominal Uang Elektronik" — jadi brand
 * ditentukan dengan mencocokkan kata kunci pada nama & operator produk.
 */
class EwalletBrands
{
    /**
     * Brand → daftar kata kunci (dicocokkan case-insensitive).
     */
    public const BRANDS = [
        'DANA' => ['dana'],
        'OVO' => ['ovo'],
        'GoPay' => ['gopay'],
        'ShopeePay' => ['shopee'],
        'LinkAja' => ['linkaja', 'link aja'],
        'GRAB' => ['grab'],
        'GO-JEK' => ['gojek', 'go-jek', 'go jek'],
        'DOKU' => ['doku'],
        'iSaku' => ['isaku'],
        'Maxim' => ['maxim'],
        'InDriver' => ['indriver'],
        'Astrapay' => ['astrapay'],
        'KasPro' => ['kaspro', 'kas pro'],
        'KAI Pay' => ['kai pay'],
    ];

    /**
     * Warna brand untuk tile di halaman E-Wallet.
     */
    public static function color(string $brand): string
    {
        return match ($brand) {
            'DANA' => '#0086FF',
            'OVO' => '#4c3494',
            'GoPay' => '#00aed6',
            'ShopeePay' => '#ee4d2d',
            'LinkAja' => '#e61c5f',
            'GRAB' => '#00b14f',
            'GO-JEK' => '#00aa13',
            'DOKU' => '#e91e63',
            'iSaku' => '#f7941e',
            'Maxim' => '#20315c',
            'InDriver' => '#e02021',
            'Astrapay' => '#8b5cf6',
            'KasPro' => '#10b981',
            'KAI Pay' => '#ef4444',
            default => '#3b82f6',
        };
    }

    /**
     * Brand untuk sebuah teks (nama produk atau operator), null jika tidak dikenal.
     */
    public static function brandOf(string $text): ?string
    {
        $text = strtolower($text);

        foreach (self::BRANDS as $brand => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $brand;
                }
            }
        }

        return null;
    }

    /**
     * Kata kunci milik sebuah brand (untuk filter query).
     *
     * @return array<int, string>
     */
    public static function keywords(string $brand): array
    {
        return self::BRANDS[$brand] ?? [];
    }
}
