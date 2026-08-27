<?php

namespace App\Support;

/**
 * Kata kunci untuk mencocokkan operator dari katalog lengkap OkeConnect.
 *
 * Di katalog lengkap, field operator produk data/pulsa tidak selalu sama
 * dengan nama operator (mis. "Data Tsel Jatim - Madura", "Tri Get More Bulanan",
 * "Mini Freedoom All Nasional", "Bronet Isi Ulang 28 Hari"), jadi pencocokan
 * dilakukan per kata kunci pada nama & operator produk.
 *
 * Kata kunci memakai frasa spesifik (bukan kata umum) agar tidak salah
 * tangkap produk operator lain, contoh: "freedom"/"freedoom" (Indosat),
 * "bronet"/"owsem"/"aigo" (AXIS), "byu" (By.U milik Telkomsel).
 */
class OperatorKeywords
{
    /**
     * Operator (nilai di OPERATOR_PREFIXES) → kata kunci pencocokan.
     */
    public const OPERATORS = [
        'Telkomsel' => [
            'telkomsel', 'tsel', 'simpati', 'kartu halo',
            'byu', 'by u', // By.U (digital brand Telkomsel)
            'voucher data combo', 'voucher data unlimited', 'voucher unlimited', // voucher data Telkomsel
        ],
        'Indosat' => [
            'indosat', 'isat',
            'freedoom', 'freedom', // Freedom/Freedoom Combo (Indosat)
            'combo attack', 'combo non attack',
            'mini data aplikasi',
        ],
        'XL' => [
            'xl',
        ],
        'AXIS' => [
            'axis',
            'bronet', // unlimited AXIS
            'owsem', // AXIS Owsem
            'aigo', // AXIS Mini Voucher / Aigo
        ],
        'Three' => [
            'three', 'tri',
        ],
        'Smartfren' => [
            'smartfren', 'smart',
            'booster +fup', // booster FUP Smartfren (frasa utuh agar tak kena "Booster" Indosat HiFi)
            'nonstop', // Smart Data Nonstop
        ],
    ];

    /**
     * Kata kunci milik sebuah operator.
     *
     * @return array<int, string>
     */
    public static function keywords(string $operator): array
    {
        return self::OPERATORS[$operator] ?? [];
    }
}
