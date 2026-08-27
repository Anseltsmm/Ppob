<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi API H2H OkeConnect (https://h2h.okeconnect.com).
 *
 * Semua request menggunakan method GET dengan parameter query:
 * memberID, pin, password, product, dest, refID, qty (open denom), check.
 */
class OkeConnectService
{
    private string $baseUrl;

    private string $memberId;

    private string $pin;

    private string $password;

    public function __construct()
    {
        $settings = Setting::getMany([
            'okeconnect_base_url',
            'okeconnect_member_id',
            'okeconnect_pin',
            'okeconnect_password',
        ], [
            'okeconnect_base_url' => 'https://h2h.okeconnect.com',
        ]);

        $this->baseUrl = rtrim((string) ($settings['okeconnect_base_url'] ?: 'https://h2h.okeconnect.com'), '/');
        $this->memberId = (string) ($settings['okeconnect_member_id'] ?? '');
        $this->pin = (string) ($settings['okeconnect_pin'] ?? '');
        $this->password = (string) ($settings['okeconnect_password'] ?? '');
    }

    public function isConfigured(): bool
    {
        return $this->memberId !== '' && $this->pin !== '' && $this->password !== '';
    }

    /**
     * Cek saldo deposit OkeConnect.
     */
    public function getBalance(): array
    {
        $response = $this->get('/trx/balance', []);

        return $this->parse($response, 'balance');
    }

    /**
     * Transaksi produk (prepaid maupun pascabayar via kode produk).
     *
     * @param  string  $product  Kode produk, contoh: T5, S20, BBSDN
     * @param  string  $dest  Nomor tujuan
     * @param  int|string  $refId  Nomor urut transaksi (unik)
     * @param  float|null  $qty  Nominal untuk transaksi open denom
     */
    public function transact(string $product, string $dest, int|string $refId, ?float $qty = null): array
    {
        $params = [
            'product' => $product,
            'dest' => $dest,
            'refID' => $refId,
        ];

        if ($qty !== null) {
            $params['qty'] = (int) $qty;
        }

        $response = $this->get('/trx', $params);

        return $this->parse($response, 'transaction');
    }

    /**
     * Cek status transaksi yang sudah pernah dikirim.
     */
    public function checkStatus(string $product, string $dest, int|string $refId, ?float $qty = null): array
    {
        $params = [
            'product' => $product,
            'dest' => $dest,
            'refID' => $refId,
            'check' => 1,
        ];

        if ($qty !== null) {
            $params['qty'] = (int) $qty;
        }

        $response = $this->get('/trx', $params);

        return $this->parse($response, 'status');
    }

    /**
     * Kirim request GET ke OkeConnect.
     */
    private function get(string $path, array $params): array
    {
        $params = array_merge([
            'memberID' => $this->memberId,
            'pin' => $this->pin,
            'password' => $this->password,
        ], $params);

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Accept' => 'text/plain'])
                ->get($this->baseUrl.$path, $params);

            return [
                'http_status' => $response->status(),
                'body' => trim($response->body()),
            ];
        } catch (\Throwable $e) {
            Log::error('OkeConnect request error', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'http_status' => 0,
                'body' => 'ERROR: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse response plain-text OkeConnect menjadi struktur terarah.
     */
    public function parse(array $response, string $context = 'transaction'): array
    {
        $body = $response['body'] ?? '';

        // Kemungkinan error dari server (bukan teks transaksi)
        if (str_contains(strtoupper($body), 'HTTP ERROR') || str_starts_with($body, 'ERROR:')) {
            return [
                'status' => 'error',
                'message' => $body,
            ];
        }

        $upper = strtoupper($body);

        // Deteksi status
        if (str_contains($upper, 'SUKSES') || str_contains($upper, 'STATUS SUKSES') || preg_match('/status\s+sukses/i', $body)) {
            $status = 'success';
        } elseif (str_contains($upper, 'GAGAL') || preg_match('/status\s+gagal/i', $body)) {
            $status = 'failed';
        } elseif (str_contains($upper, 'MENUNGGU JAWABAN') || str_contains($upper, 'PENDING') || str_contains($upper, 'MENUNGGU') || str_contains($upper, 'DIPROSES')) {
            $status = 'pending';
        } elseif (str_contains($upper, 'TIDAK ADA') || str_contains($upper, 'NO DATA') || str_contains($upper, 'TIDAK ADA DATA')) {
            $status = 'nodata';
        } else {
            $status = 'unknown';
        }

        // Ekstrak nomor transaksi T#
        preg_match('/T#(\d+)/', $body, $mTrx);
        // Ekstrak SN
        preg_match('/SN\/?Ref:?\s*:?\s*([A-Z0-9.\-]+)/i', $body, $mSn1);
        preg_match('/SN:\s*([A-Z0-9.\-]+)/i', $body, $mSn2);
        $sn = isset($mSn1[1]) || isset($mSn2[1]) ? rtrim($mSn1[1] ?? $mSn2[1], '.') : null;

        // Ambil pesan alasan untuk GAGAL (setelah "GAGAL.")
        $reason = null;
        if ($status === 'failed') {
            if (preg_match('/GAGAL\.?\s*(.*?)(?:\.?\s*Saldo|\s*@\d{2}\/\d{2}|\s*$)/i', $body, $mReason)) {
                $reason = trim($mReason[1]);
            }
        }

        // Ambil saldo terakhir jika ada (dukung format 1.234.567 atau 1.234.567,89)
        $saldo = null;
        if (preg_match('/Saldo\s+([\d.,]+)/', $body, $mSaldo)) {
            $raw = $mSaldo[1];
            $saldo = str_contains($raw, ',')
                ? (float) str_replace(',', '.', str_replace('.', '', $raw))
                : (float) str_replace(',', '', $raw);
        }

        return [
            'status' => $status,
            'trx_id' => $mTrx[1] ?? null,
            'sn' => $sn,
            'reason' => $reason,
            'saldo' => $saldo,
            'raw' => $body,
        ];
    }

    /**
     * Parse respons inquiry tagihan (produk kode C): ambil nama pelanggan
     * dan nominal tagihan dari teks respons.
     *
     * Format respons inquiry bervariasi per biller, jadi pencarian dibuat
     * fleksibel: cari label (Tagihan/Total/Jumlah/Nominal) lalu nilai Rupiah.
     */
    public function parseInquiry(array $response): array
    {
        // Terima hasil transact() (sudah di-parse, berisi 'raw') atau
        // respons mentah dari get() (berisi 'body').
        $body = (string) ($response['body'] ?? $response['raw'] ?? '');
        $parsed = $this->parse(['body' => $body], 'inquiry');

        // Nama pelanggan
        $name = null;
        if (preg_match('/(?:NAMA\s*(?:PELANGGAN)?|PELANGGAN|CUSTOMER|ATAS NAMA)\s*[:=]?\s*([A-Za-z][A-Za-z .\'-]{1,60})/i', $body, $mName)) {
            $name = trim($mName[1]);
        }

        // Nominal tagihan
        $nominal = null;
        if (preg_match('/(?:TAGIHAN|TOTAL|JUMLAH|NOMINAL|TAGIHAN ANDA)\s*[:=]?\s*(?:Rp\.?\s*)?([\d.,]+)/i', $body, $mNom)) {
            $nominal = $this->toNumber($mNom[1]);
        } elseif (preg_match('/Rp\.?\s*([\d.,]+)/i', $body, $mRp)) {
            $nominal = $this->toNumber($mRp[1]);
        } else {
            // Fallback: nilai terbesar yang terlihat seperti nominal (>= 1.000)
            preg_match_all('/(?<![\d.,])(\d{1,3}(\.\d{3})+)(?![\d.,])/', $body, $mAll);
            foreach (array_reverse($mAll[1] ?? []) as $candidate) {
                $value = $this->toNumber($candidate);
                if ($value >= 1000 && $value > ($nominal ?? 0)) {
                    $nominal = $value;
                }
            }
        }

        return [
            'status' => $parsed['status'],
            'trx_id' => $parsed['trx_id'],
            'sn' => $parsed['sn'],
            'reason' => $parsed['reason'],
            'customer_name' => $name,
            'nominal' => $nominal !== null ? (float) $nominal : null,
            'raw' => $body,
        ];
    }

    /**
     * Ubah angka Rupiah ("1.234.567" atau "1.234.567,89") jadi float.
     */
    private function toNumber(string $raw): float
    {
        $raw = trim($raw);

        if (str_contains($raw, ',')) {
            return (float) str_replace(',', '.', str_replace('.', '', $raw));
        }

        return (float) str_replace('.', '', $raw);
    }
}
