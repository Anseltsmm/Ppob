<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi Payment Gateway TriPay (https://tripay.co.id/developer).
 *
 * Mode sandbox: https://tripay.co.id/api-sandbox
 * Mode production: https://tripay.co.id/api
 *
 * Kredensial diambil dari tabel settings:
 * tripay_api_key, tripay_private_key, tripay_merchant_code, tripay_mode
 */
class TripayService
{
    private string $apiKey;
    private string $privateKey;
    private string $merchantCode;
    private string $baseUrl;

    public function __construct()
    {
        $settings = Setting::getMany([
            'tripay_api_key',
            'tripay_private_key',
            'tripay_merchant_code',
            'tripay_mode',
        ], [
            'tripay_mode' => 'sandbox',
        ]);

        $this->apiKey = (string) ($settings['tripay_api_key'] ?? '');
        $this->privateKey = (string) ($settings['tripay_private_key'] ?? '');
        $this->merchantCode = (string) ($settings['tripay_merchant_code'] ?? '');
        $mode = ($settings['tripay_mode'] ?? 'sandbox') === 'production' ? '' : '-sandbox';
        $this->baseUrl = 'https://tripay.co.id/api'.$mode;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->privateKey !== '' && $this->merchantCode !== '';
    }

    public function mode(): string
    {
        return str_contains($this->baseUrl, 'sandbox') ? 'sandbox' : 'production';
    }

    /**
     * Daftar channel pembayaran aktif (dengan cache 5 menit).
     */
    public function paymentChannels(bool $force = false): array
    {
        $cacheKey = 'tripay.channels';
        if ($force) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, function () {
            $result = $this->get('/merchant/payment-channel', []);

            return $result['data'] ?? [];
        });
    }

    /**
     * Hitung fee transaksi per channel.
     */
    public function feeCalculator(int $amount, ?string $code = null): array
    {
        $params = ['amount' => $amount];
        if ($code) {
            $params['code'] = $code;
        }

        $result = $this->get('/merchant/fee-calculator', $params);

        return $result['data'] ?? [];
    }

    /**
     * Instruksi pembayaran untuk sebuah channel.
     */
    public function paymentInstruction(string $code, ?string $payCode = null, ?int $amount = null): array
    {
        $params = ['code' => $code];
        if ($payCode) {
            $params['pay_code'] = $payCode;
        }
        if ($amount) {
            $params['amount'] = $amount;
        }

        $result = $this->get('/payment/instruction', $params);

        return $result['data'] ?? [];
    }

    /**
     * Buat transaksi closed payment.
     *
     * @param  array  $customer  [name, email, phone]
     * @param  array  $orderItems  [[sku, name, price, quantity], ...]
     * @param  array  $options  [callback_url, return_url, expired_time]
     */
    public function createTransaction(
        string $method,
        string $merchantRef,
        int $amount,
        array $customer,
        array $orderItems,
        array $options = []
    ): array {
        $payload = [
            'method' => $method,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $customer['name'] ?? '',
            'customer_email' => $customer['email'] ?? '',
            'customer_phone' => $customer['phone'] ?? '',
            'order_items' => $orderItems,
            'signature' => $this->signature($merchantRef, $amount),
        ];

        if (! empty($options['callback_url'])) {
            $payload['callback_url'] = $options['callback_url'];
        }
        if (! empty($options['return_url'])) {
            $payload['return_url'] = $options['return_url'];
        }
        if (! empty($options['expired_time'])) {
            $payload['expired_time'] = $options['expired_time'];
        }

        $result = $this->post('/transaction/create', $payload);

        return $result['data'] ?? [];
    }

    /**
     * Detail transaksi berdasarkan reference Tripay.
     */
    public function detailTransaction(string $reference): array
    {
        $result = $this->get('/transaction/detail', ['reference' => $reference]);

        return $result['data'] ?? [];
    }

    /**
     * Cek status transaksi (wrapper detail).
     */
    public function checkStatus(string $reference): ?string
    {
        $data = $this->detailTransaction($reference);

        return $data['status'] ?? null;
    }

    /**
     * Signature untuk request transaksi:
     * hash_hmac('sha256', merchant_code . merchant_ref . amount, private_key)
     */
    public function signature(string $merchantRef, int|float $amount): string
    {
        return hash_hmac('sha256', $this->merchantCode.$merchantRef.$amount, $this->privateKey);
    }

    /**
     * Validasi signature callback Tripay:
     * hash_hmac('sha256', merchant_code . amount_received . merchant_ref, private_key)
     */
    public function verifyCallback(string $signature, string $merchantRef, int|float $amountReceived): bool
    {
        $expected = hash_hmac('sha256', $this->merchantCode.$amountReceived.$merchantRef, $this->privateKey);

        return hash_equals($expected, $signature);
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    private function get(string $path, array $params): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->get($this->baseUrl.$path, $params);

            return $this->handle($response->json() ?? [], $path);
        } catch (\Throwable $e) {
            Log::error('Tripay GET error', ['path' => $path, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function post(string $path, array $payload): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->headers())
                ->post($this->baseUrl.$path, $payload);

            return $this->handle($response->json() ?? [], $path);
        } catch (\Throwable $e) {
            Log::error('Tripay POST error', ['path' => $path, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function handle(array $json, string $path): array
    {
        if (! ($json['success'] ?? false)) {
            Log::warning('Tripay API returned error', [
                'path' => $path,
                'message' => $json['message'] ?? 'unknown',
            ]);
        }

        return $json;
    }
}
