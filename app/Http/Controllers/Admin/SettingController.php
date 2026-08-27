<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\OkeConnectService;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $keys = [
            // OkeConnect
            'okeconnect_base_url', 'okeconnect_member_id', 'okeconnect_pin', 'okeconnect_password', 'okeconnect_callback_token',
            // Tripay
            'tripay_api_key', 'tripay_private_key', 'tripay_merchant_code', 'tripay_mode',
            // QRIS & Info
            'qris_payload',
            'app_info_phone', 'app_info_whatsapp', 'app_info_email', 'app_info_address', 'app_info_hours',
        ];

        $settings = Setting::getMany($keys, [
            'okeconnect_base_url' => 'https://h2h.okeconnect.com',
            'tripay_mode' => 'sandbox',
        ]);

        $serverIp = $this->serverIp();

        // Status konfigurasi tiap provider (untuk indikator visual di halaman)
        $okeconnectConfigured = (new OkeConnectService)->isConfigured();
        $tripayConfigured = (new TripayService)->isConfigured();

        return view('admin.settings.index', compact('settings', 'serverIp', 'okeconnectConfigured', 'tripayConfigured'));
    }

    /**
     * IP publik VPS (di-cache 6 jam) — dipakai untuk whitelist IP callback
     * di dashboard OkeConnect. Fallback ke IP lokal server jika layanan
     * publik tidak bisa diakses.
     */
    private function serverIp(): ?string
    {
        return Cache::remember('server.public_ip', 6 * 60 * 60, function () {
            try {
                $response = Http::timeout(5)->get('https://api.ipify.org');
                $ip = trim((string) $response->body());

                if ($response->successful() && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            } catch (\Throwable) {
                // lanjut ke fallback IP lokal
            }

            $local = gethostbyname(gethostname());

            return filter_var($local, FILTER_VALIDATE_IP) ? $local : null;
        });
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'okeconnect_base_url' => ['nullable', 'url', 'max:255'],
            'okeconnect_member_id' => ['nullable', 'string', 'max:50'],
            'okeconnect_pin' => ['nullable', 'string', 'max:50'],
            'okeconnect_password' => ['nullable', 'string', 'max:100'],
            'tripay_api_key' => ['nullable', 'string', 'max:255'],
            'tripay_private_key' => ['nullable', 'string', 'max:255'],
            'tripay_merchant_code' => ['nullable', 'string', 'max:50'],
            'tripay_mode' => ['nullable', 'in:sandbox,production'],
            'qris_payload' => ['nullable', 'string'],
            'app_info_phone' => ['nullable', 'string', 'max:50'],
            'app_info_whatsapp' => ['nullable', 'string', 'max:50'],
            'app_info_email' => ['nullable', 'email', 'max:100'],
            'app_info_address' => ['nullable', 'string', 'max:255'],
            'app_info_hours' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Bersihkan cache channel Tripay
        Cache::forget('tripay.channels');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function checkBalance(OkeConnectService $okeconnect)
    {
        if (! $okeconnect->isConfigured()) {
            return back()->with('error', 'Kredensial OkeConnect belum diatur lengkap.');
        }

        $result = $okeconnect->getBalance();
        $balance = null;

        // Response balance berupa teks, contoh: "SALDO ANDA: 10.000.000"
        if (preg_match('/[\d.,]+/', $result['body'] ?? '', $m)) {
            $balance = $result['body'];
        }

        return back()->with('info', 'Response OkeConnect: '.($result['body'] ?? '(kosong)'));
    }

    public function testTripay(TripayService $tripay)
    {
        if (! $tripay->isConfigured()) {
            return back()->with('error', 'Kredensial Tripay belum diatur lengkap.');
        }

        $channels = $tripay->paymentChannels(true);

        if (empty($channels)) {
            return back()->with('error', 'Tripay tidak mengembalikan channel. Cek API Key / mode sandbox Anda.');
        }

        return back()->with('success', 'Tripay terhubung! '.count($channels).' channel pembayaran aktif ditemukan.');
    }

    public function regenerateCallbackToken()
    {
        Setting::set('okeconnect_callback_token', Str::random(32));

        return back()->with('success', 'Token callback OkeConnect diganti. Perbarui URL callback di dashboard OkeConnect dengan token baru.');
    }
}
