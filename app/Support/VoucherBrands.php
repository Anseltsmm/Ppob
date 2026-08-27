<?php

namespace App\Support;

/**
 * Deteksi brand/operator produk Cetak Voucher dari nama/operator OkeConnect.
 *
 * Operator dari OkeConnect berbentuk \"Tsel Cetak Voucher Jatim\",
 * \"Isat Cetak Freedom Internet\", \"XL Cetak Voucher Flex Mini\" — brand
 * ditentukan dengan mencocokkan kata kunci pada nama & operator produk.
 * Wilayah (Jatim, Jabotabek, dll.) biasanya ada di belakang nama operator.
 */
class VoucherBrands
{
    /**
     * Brand → daftar kata kunci (dicocokkan case-insensitive).
     */
    public const BRANDS = [
        'Telkomsel' => ['tsel', 'telkomsel'],
        'Indosat' => ['isat', 'indosat', 'freedom'],
        'XL' => ['xl '],
        'Three' => ['tri ', 'tri-'],
        'Smartfren' => ['smart'],
        'By.U' => ['byu'],
        'AXIS' => ['axis', 'aigo'],
    ];

    /**
     * Warna brand untuk tile di halaman menu.
     */
    public const COLORS = [
        'Telkomsel' => '#ef4444',
        'Indosat' => '#8b5cf6',
        'XL' => '#ec4899',
        'Three' => '#6366f1',
        'Smartfren' => '#06b6d4',
        'By.U' => '#f59e0b',
        'AXIS' => '#22c55e',
    ];

    /**
     * Brand untuk sebuah teks (nama produk atau operator), null jika tidak dikenal.
     */
    public static function brandOf(string $text): ?string
    {
        $text = strtolower(' '.trim($text).' ');

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

    /**
     * Warna sebuah brand (fallback biru).
     */
    public static function color(string $brand): string
    {
        return self::COLORS[$brand] ?? '#3b82f6';
    }

    /**
     * Wilayah dari operator, contoh \"Tsel Cetak Voucher Jatim\" → \"Jatim\",
     * \"Tri Cetak Vcr Happy West Java\" → \"West Java\". null jika tidak ada.
     */
    public static function regionOf(?string $operator): ?string
    {
        $text = trim((string) $operator);
        if ($text === '') {
            return null;
        }

        // Buang kata kunci brand & kata umum
        $clean = $text;
        foreach (['cetak', 'voucher', 'vcr', 'perdana', 'mini', 'happy', 'aigo', 'internet', 'unli', 'unlimited', 'flex', 'combo', 'harian', 'bulanan', 'nonstop', 'jumbo', 'roaming', 'istimewa', 'super', 'promo', 'warnet', 'sosmed', 'hotrod', 'bebas', 'puas', 'freedom', 'hot'] as $word) {
            $clean = preg_replace('/\b'.preg_quote($word, '/').'\b/i', ' ', $clean);
        }

        // Buang brand penuh, nominal singkatan (5K), dan generasi (5G)
        $clean = preg_replace('/\b(tsel|telkomsel|isat|indosat|xl|tri|smart|smartfren|byu|axis|aigo)\b/i', ' ', $clean);
        $clean = preg_replace('/\b\d+\s*k\b|\b[45]g\b|\bsatspam\+?\b/i', ' ', $clean);
        $clean = trim(preg_replace('/[+\-]+/u', '', $clean));
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        return $clean !== '' ? $clean : null;
    }
}
