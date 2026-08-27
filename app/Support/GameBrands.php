<?php

namespace App\Support;

/**
 * Deteksi brand game dari nama produk / operator OkeConnect.
 *
 * Operator dari OkeConnect berbentuk "TPG Diamond Mobile Legends",
 * "TPG Game Vcr Roblox", "TPG Google Play ID" — brand game ditentukan
 * dengan mencocokkan kata kunci pada nama & operator produk.
 */
class GameBrands
{
    /**
     * Brand → daftar kata kunci (dicocokkan case-insensitive).
     * Urutan penting: brand dengan kata kunci yang lebih spesifik di depan.
     */
    public const BRANDS = [
        'Mobile Legends' => ['mobile legends', 'mlbb'],
        'Free Fire' => ['free fire'],
        'PUBG' => ['pubg'],
        'Garena' => ['garena'],
        'Gemscool' => ['gemschool', 'g-cash'],
        'Roblox' => ['roblox'],
        'Steam' => ['steam'],
        'Google Play' => ['google play'],
        'Razer Gold' => ['razer gold'],
        'Unipin' => ['unipin'],
        'Point Blank' => ['point blank'],
        'Honor of Kings' => ['honor of kings'],
        'Clash of Clans' => ['clash of clans'],
        'Clash Royale' => ['clash royale'],
        'Call of Duty' => ['call of duty'],
        'Arena of Valor' => ['arena of valor'],
        'Arena Breakout' => ['arena breakout'],
        'Life After' => ['life after'],
        'Lokapala' => ['lokapala'],
        'Magic Chess' => ['magic chess'],
        'One Punch Man' => ['one punch man'],
        'Werewolf' => ['werewolf'],
        'Zepeto' => ['zepeto'],
        'Speed Drifter' => ['speed drifter'],
        'Blood Strike' => ['blood strike'],
        'Delta Force' => ['delta force'],
        'FC Mobile' => ['fc mobile'],
        'Okegaming' => ['okegaming'],
        'Vidio' => ['vidio'],
        'Spotify' => ['spotify'],
        'Genflix' => ['genflix'],
        'WeTV' => ['wetv'],
    ];

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
