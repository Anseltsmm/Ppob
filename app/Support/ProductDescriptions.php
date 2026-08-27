<?php

namespace App\Support;
/**
 * Generator deskripsi produk yang informatif (bukan sekadar duplikat nama).
 *
 * Dipakai saat import produk dari OkeConnect dan untuk backfill produk lama.
 * Item array mengikuti bentuk katalog dari OkeConnectCatalogService::fetch():
 * name, operator, category, modal_price, open_denom, inquiry_code.
 */
class ProductDescriptions
{
    /**
     * @param  array{name?: string, operator?: string, category?: string, open_denom?: bool, inquiry_code?: string|null}  $item
     */
    public static function forItem(array $item): string
    {
        $name = trim((string) ($item['name'] ?? ''));
        $operator = trim((string) ($item['operator'] ?? ''));
        $category = (string) ($item['category'] ?? '');
        $text = strtolower($operator.' '.$name);
        $isOpen = (bool) ($item['open_denom'] ?? false);
        $isBill = ($item['inquiry_code'] ?? null) !== null || $category === 'Pascabayar';

        if ($isOpen) {
            $brand = EwalletBrands::brandOf($text) ?: GameBrands::brandOf($text);
            $label = $brand ?: ($category === 'Game' ? 'game' : 'e-wallet');

            if ($category === 'Game') {
                return "Voucher $label nominal bebas — isi nominal sesuai keinginan. Biaya admin berlaku, kode voucher dikirim setelah pembayaran sukses.";
            }

            return "Top up saldo $label nominal bebas — isi nominal sesuai keinginan. Biaya admin berlaku, saldo masuk setelah pembayaran sukses.";
        }

        if ($isBill) {
            return "Pembayaran tagihan: $name. Nominal tagihan didapat dari hasil cek tagihan, pembayaran diproses setelah konfirmasi.";
        }

        if ($category === 'Token PLN') {
            return "Token listrik prabayar PLN senilai ".self::amount($name).". Pastikan nomor meter (ID pelanggan) sudah benar — token yang sudah terkirim tidak dapat dikembalikan.";
        }

        if ($category === 'E-Wallet') {
            $brand = EwalletBrands::brandOf($text) ?: 'e-wallet tujuan';

            return "Top up saldo $brand senilai ".self::amount($name).". Pastikan nomor tujuan terdaftar di aplikasi $brand, saldo masuk setelah pembayaran sukses.";
        }

        if ($category === 'Game') {
            $brand = GameBrands::brandOf($text) ?: 'game';

            return "Voucher $brand — pastikan User ID (ID pemain) sudah benar. Kode voucher dikirim setelah pembayaran sukses.";
        }

        if ($category === 'Paket Data') {
            $denom = self::dataDenom($name);

            return 'Paket data '.self::cleanOperatorPrefix($operator).($denom !== '' ? " — $denom" : '').'. Pastikan nomor tujuan benar, paket aktif setelah pembayaran sukses.';
        }

        if ($category === 'Cetak Voucher') {
            $brand = VoucherBrands::brandOf($text) ?: 'operator';
            $denom = self::dataDenom($name);

            return 'Voucher '.$brand.' untuk diaktifkan sendiri'.($denom !== '' ? " ($denom)" : '').'. Setelah pembayaran sukses, kode voucher (SN) muncul di halaman order — gunakan untuk aktivasi sesuai ketentuan operator.';
        }

        if ($category === 'Pulsa Transfer') {
            return 'Transfer pulsa '.($operator ?: 'antar nomor').' senilai '.self::amount($name).'. Pastikan nomor tujuan benar, pulsa masuk beberapa saat setelah pembayaran sukses.';
        }

        // Kategori Pulsa: Masa Aktif / SMS & Telepon / Pulsa reguler
        if (str_contains($text, 'masa aktif')) {
            $period = self::period($name);
            $op = self::operatorClean($operator);
            // Operator katalog hanya "+Masa Aktif" — brand sebenarnya ada di nama
            if (in_array($op, ['operator', '+'], true)) {
                $op = self::brandFromMasaAktifName($name);
            }

            return 'Perpanjangan masa aktif nomor '.$op.' selama '.($period ?: 'masa aktif yang dipilih').'. Berlaku setelah pembayaran sukses — pastikan nomor tujuan benar.';
        }

        if (str_contains($text, 'sms') || str_contains($text, 'telepon') || str_contains($text, 'telp')) {
            $denom = self::smsDenom($name);

            return 'Paket SMS/Telepon '.self::cleanOperatorSms($operator).($denom !== '' ? " ($denom)" : '').'. Pastikan nomor tujuan benar, paket aktif otomatis setelah pembayaran sukses.';
        }

        return 'Pulsa '.($operator ?: 'pulsa').' senilai '.self::amount($name).'. Pastikan nomor tujuan sudah benar, pulsa masuk beberapa saat setelah pembayaran sukses.';
    }

    /**
     * Nominal Rupiah dari nama produk, contoh "Telkomsel 5.000" → "Rp 5.000",
     * "Token PLN 1 Juta" → "Rp 1.000.000".
     */
    private static function amount(string $name): string
    {
        // Satuan kata: "20 Ribu" → 20.000, "1 Juta" → 1.000.000, "1,5 Juta" → 1.500.000, "100rb" → 100.000
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(ribu|juta|miliar|rb|jt)\b/i', $name, $m)) {
            $multiplier = ['ribu' => 1000, 'rb' => 1000, 'juta' => 1_000_000, 'jt' => 1_000_000, 'miliar' => 1_000_000_000][strtolower($m[2])];
            $val = (float) str_replace(',', '.', $m[1]) * $multiplier;

            return 'Rp '.number_format((int) round($val), 0, ',', '.');
        }

        // Angka penuh dengan pemisah ribuan: "1.000.000" / "20.000" (hindari digit dalam kata seperti H2H)
        if (preg_match('/(?<![\d.])(\d{1,3}(?:\.\d{3})+)(?![\d.])/', $name, $m)) {
            $val = (int) str_replace('.', '', $m[1]);
            if ($val > 0) {
                return 'Rp '.number_format($val, 0, ',', '.');
            }
        }

        // Angka polos minimal 3 digit: "5000" / "15000" (bukan digit dalam kata)
        if (preg_match('/(?<![\d.])(\d{3,})(?![\d.])/', $name, $m)) {
            $val = (int) $m[1];
            if ($val > 0) {
                return 'Rp '.number_format($val, 0, ',', '.');
            }
        }

        return 'nominal terkait';
    }

    /**
     * Denom paket data, contoh "Smart 30GB All..." → "30 GB".
     */
    private static function dataDenom(string $name): string
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(gb|mb|tb)/i', $name, $m)) {
            return $m[1].' '.strtoupper($m[2]);
        }

        return '';
    }

    /**
     * Periode masa aktif, contoh "+ 30 Hari" → "30 Hari".
     */
    private static function period(string $name): string
    {
        if (preg_match('/\+\s*(\d+)\s*(hari|bulan|tahun)/i', $name, $m)) {
            return $m[1].' '.ucfirst(strtolower($m[2]));
        }

        return '';
    }

    /**
     * Denom SMS/Telepon, contoh "300 SMS" / "60 Mnt" / "185 Menit".
     */
    private static function smsDenom(string $name): string
    {
        if (preg_match('/(\d+(?:\.\d{3})*)\s*(sms|mnt|menit)/i', $name, $m)) {
            $unit = strtolower($m[2]);
            $unit = $unit === 'sms' ? 'SMS' : ($unit === 'mnt' ? 'Mnt' : ucfirst($unit));

            return number_format((int) str_replace('.', '', $m[1]), 0, ',', '.').' '.$unit;
        }

        return '';
    }

    /**
     * Bersihkan operator paket data dari awalan data/voucher/kuota,
     * contoh "Data Smart Combo" → "Smart Combo", "Voucher Data Byu" → "Byu".
     */
    private static function cleanOperatorPrefix(string $operator): string
    {
        $clean = preg_replace('/^(data|voucher|kuota)\s+/i', '', trim($operator));
        $clean = preg_replace('/^(data|voucher|kuota)\s+/i', '', (string) $clean);

        return trim((string) $clean) ?: 'operator';
    }

    /**
     * Bersihkan operator SMS/Telepon, contoh "SMS Telepon Indosat" → "Indosat",
     * "Tsel Telepon New" → "Tsel New".
     */
    private static function cleanOperatorSms(string $operator): string
    {
        $clean = preg_replace('/\b(sms|telepon|telp|telephone)\b/i', ' ', $operator);
        $clean = preg_replace('/\s+/', ' ', (string) $clean);

        return trim((string) $clean) ?: 'operator';
    }

    /**
     * Bersihkan operator masa aktif ("+Masa Aktif Telkomsel" → "Telkomsel").
     */
    private static function operatorClean(string $operator): string
    {
        $clean = preg_replace('/\+\s*masa aktif\s*/i', '', $operator);
        $clean = preg_replace('/masa aktif\s*/i', '', (string) $clean);

        return trim((string) $clean) ?: 'operator';
    }

    /**
     * Brand dari nama produk masa aktif,
     * contoh "Masa Aktif Telkomsel + 10 Hari" → "Telkomsel",
     * "Masa Aktif Kartu Tri + 4 Bulan" → "Kartu Tri".
     */
    private static function brandFromMasaAktifName(string $name): string
    {
        if (preg_match('/masa aktif\s+([a-z0-9 .\-]+?)(?:\s*\+\s*\d+\s*(?:hari|bulan|tahun)|$)/i', $name, $m)) {
            $brand = trim($m[1]);
            if ($brand !== '') {
                return $brand;
            }
        }

        return 'operator';
    }
}
