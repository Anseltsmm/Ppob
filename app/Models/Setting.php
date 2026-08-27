<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /**
     * Ambil nilai setting, dengan cache.
     */
    /**
     * Cache duration: 30 hari (2.592.000 detik).
     * Cache akan di-clear oleh middleware saat user membuka aplikasi (sekali per session).
     */
    private const CACHE_TTL = 2_592_000; // 30 hari

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Simpan (atau update) setting, lalu bersihkan cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Ambil banyak setting sekaligus sebagai array asosiatif.
     */
    public static function getMany(array $keys, array $defaults = []): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::get($key, $defaults[$key] ?? null);
        }

        return $result;
    }

    /**
     * Hapus semua cache setting agar data terbaru dibaca dari database.
     * Dipanggil oleh middleware RefreshSettingsCache (sekali per session).
     * Menggunakan try-catch agar aman saat tabel belum ada (misal testing).
     */
    public static function clearCache(): void
    {
        try {
            static::pluck('key')->each(function (string $key) {
                Cache::forget("setting.{$key}");
            });
        } catch (\Throwable) {
            // Tabel belum ada — skip
        }
    }
}
