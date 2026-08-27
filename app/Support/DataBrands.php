<?php

namespace App\Support;

/**
 * Deteksi sub-brand produk Paket Data dari field operator OkeConnect.
 *
 * Operator dari katalog berbentuk \"XL Bebas Puas 5K\", \"Indosat Freedom Unlimited\",
 * \"Axis Data Cuanku Spesial\", \"Tri Data Happy New\" — sub-brand (Cuanku, Freedom,
 * Bebas Puas, Happy, dll.) diambil dari operator, di-scope per operator utama
 * (sesuai OPERATOR_PREFIXES di PulsaController) agar kata yang sama tidak bentrok
 * antar operator (mis. \"Combo\" ada di XL & Tri, \"Mini Data\" di Indosat & Smartfren).
 *
 * Urutan daftar penting: keyword yang lebih spesifik dulu (mis. \"Combo Flex\"
 * sebelum \"Flex\") supaya produk tidak salah label.
 */
class DataBrands
{
    /**
     * Operator utama → sub-brand → kata kunci (dicocokkan case-insensitive).
     */
    public const SUB_BRANDS = [
        'Telkomsel' => [
            'By.U' => ['byu'],
            'Voucher Tsel' => ['voucher tsel'],
            'Voucher Unlimited' => ['voucher unlimited', 'voucher data unlimited'],
            'Data AS' => ['data as'],
            'LOOP' => ['loop'],
            'SIMPATI' => ['simpati'],
            'Data Bulanan' => ['data bulanan'],
            'Eksklusif' => ['eksklusif'],
            'Terbaik' => ['terbaik'],
            'Paket Data' => ['paket data'],
        ],
        'Indosat' => [
            'Freedom' => ['freedom', 'freedoom'],
            'Combo' => ['combo'],
            'Data Bulanan' => ['data bulanan'],
            'Mini Data' => ['mini data'],
            'Gaspol' => ['gaspol'],
            'SATSPAM+' => ['satspam'],
            'Only 4U' => ['only 4u'],
            'CVM' => ['cvm'],
            'Yellow' => ['yellow'],
            'HiFi' => ['hifi'],
            'Umroh Haji' => ['umroh', 'haji'],
            'Istimewa' => ['istimewa'],
            'Roaming' => ['roaming'],
        ],
        'XL' => [
            'Bebas Puas' => ['bebas puas'],
            'Cuanku' => ['cuanku'],
            'Combo X-Tra' => ['combo x-tra', 'x-tra kuota', 'xtra combo'],
            'Combo Flex' => ['combo flex'],
            'Flex Mini' => ['flex mini'],
            'Flex Max' => ['flex max'],
            'Voucher Flex' => ['voucher flex'],
            'Pure Data' => ['pure data'],
            'Big Data' => ['big data'],
            'Data Reguler' => ['data reguler'],
            'Hotrod' => ['hotrod'],
            'Harian' => ['harian'],
            'Games' => ['games'],
            'Ultra 5G' => ['ultra 5g'],
            'Umroh Haji' => ['umroh', 'haji'],
            'Roaming' => ['roaming'],
            'Pass' => ['pass'],
            'Akrab' => ['akrab'],
            'Enterprise' => ['enterprise'],
            'Blue' => ['blue'],
            'Data Max' => ['data max'],
            'Mingguan' => ['mingguan'],
            'VIP' => ['vip'],
        ],
        'AXIS' => [
            'Cuanku' => ['cuanku'],
            'Bronet' => ['bronet'],
            'Owsem' => ['owsem'],
            'On You SKS' => ['on you sks'],
            'Mini Isi Ulang' => ['mini isi ulang'],
            'Mini Voucher' => ['mini voucher'],
            'Aigo' => ['aigo'],
            'DRP' => ['drp'],
            'KUSA' => ['kusa'],
            'Data Max' => ['data max'],
            'Paket Warnet' => ['warnet'],
            'Roaming' => ['roaming'],
        ],
        'Three' => [
            'Umroh Haji' => ['umroh', 'haji'],
            'Mini Happy' => ['mini happy'],
            'Happy Mini' => ['happy mini'],
            'Happy' => ['happy'],
            'AON' => ['aon'],
            'Cuan Max' => ['cuan max'],
            'Keep On Streaming' => ['keep on streaming'],
            'H3RO' => ['h3ro'],
            'HiFi' => ['hifi'],
            'All Jaringan' => ['all jaringan'],
            'Games' => ['games'],
            'Combo' => ['combo'],
            '4G LTE' => ['4g lte'],
            'Get More' => ['get more'],
            'Data Mini' => ['data mini'],
            'Harian' => ['harian'],
            'Modem' => ['modem'],
            'Bulanan' => ['bulanan'],
            'Roaming' => ['roaming'],
            'Mini' => ['mini'],
        ],
        'Smartfren' => [
            'Nonstop' => ['nonstop'],
            'Unlimited' => ['unlimited'],
            'Jumbo' => ['jumbo'],
            'Volume Based' => ['volume based'],
            'Combo' => ['combo'],
            'Mini Data' => ['mini data'],
            'Modem' => ['modem'],
            'Streaming' => ['streaming', 'sosial media'],
            'Super Kuota' => ['super kuota'],
            'Roaming' => ['roaming'],
            'Lokal' => ['lokal'],
            '5G' => ['5g'],
            'Paket Data' => ['paket data'],
        ],
    ];

    /**
     * Sub-brand dari teks (operator + nama produk) untuk sebuah operator utama.
     * null jika tidak cocok sub-brand mana pun (produk reguler).
     */
    public static function brandOf(string $text, string $operator): ?string
    {
        $text = ' '.strtolower(trim($text)).' ';

        foreach (self::SUB_BRANDS[$operator] ?? [] as $brand => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $brand;
                }
            }
        }

        return null;
    }

    /**
     * Kata kunci sebuah sub-brand untuk operator tertentu.
     *
     * @return array<int, string>
     */
    public static function keywords(string $operator, string $brand): array
    {
        return self::SUB_BRANDS[$operator][$brand] ?? [];
    }
}
