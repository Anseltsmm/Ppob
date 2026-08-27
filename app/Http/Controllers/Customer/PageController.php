<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class PageController extends Controller
{
    /**
     * Halaman QRIS: menampilkan kode QRIS merchant untuk pembayaran.
     */
    public function qris()
    {
        $payload = Setting::get('qris_payload');

        return view('customer.qris', compact('payload'));
    }

    /**
     * Halaman Info: informasi layanan.
     */
    public function info()
    {
        $settings = Setting::getMany([
            'app_info_name',
            'app_info_phone',
            'app_info_whatsapp',
            'app_info_email',
            'app_info_address',
            'app_info_hours',
        ]);

        return view('customer.info', compact('settings'));
    }
}
