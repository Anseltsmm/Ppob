<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\OkeConnectService;
use App\Support\BillTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagihanController extends Controller
{
    /**
     * Halaman pembayaran tagihan & pascabayar.
     *
     * Produk yang tampil: produk bayar (memiliki inquiry_code) yang aktif,
     * dikelompokkan per jenis layanan (PLN, PDAM, PBB, ...) lalu per biller.
     */
    public function index()
    {
        $products = Product::whereNotNull('inquiry_code')
            ->where('status', true)
            ->orderBy('operator')
            ->orderBy('name')
            ->get();

        $types = [];
        foreach ($products as $product) {
            $type = BillTypes::detect(($product->operator ?? '').' '.$product->name);
            $types[$type][$product->operator ?: 'Lainnya'][] = $product;
        }
        ksort($types);

        // Data biller untuk filter & JS (dibuat di controller agar aman untuk @json di Blade)
        $billers = [];
        foreach ($types as $type => $groups) {
            $billers[$type] = [];
            foreach ($groups as $operator => $groupProducts) {
                foreach ($groupProducts as $product) {
                    $billers[$type][] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'operator' => $operator,
                        'description' => $product->description,
                    ];
                }
            }
        }

        return view('customer.tagihan.index', compact('types', 'billers'));
    }

    /**
     * Cek tagihan (inquiry) ke OkeConnect. Tidak memotong saldo — gratis.
     */
    public function inquiry(Request $request, OkeConnectService $okeconnect)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'destination' => ['required', 'string', 'max:30'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if (! $product->isBill()) {
            return response()->json(['error' => 'Produk bukan layanan tagihan.'], 422);
        }

        if (! $okeconnect->isConfigured()) {
            return response()->json(['error' => 'Layanan tagihan sedang dinonaktifkan sementara. Hubungi admin.'], 422);
        }

        $refId = 'INQ'.date('ymdHis').strtoupper(Str::random(4));
        $info = $okeconnect->parseInquiry(
            $okeconnect->transact((string) $product->inquiry_code, $validated['destination'], $refId)
        );

        if ($info['status'] !== 'success') {
            return response()->json([
                'error' => $info['reason'] ?: 'Cek tagihan gagal. Silakan coba lagi.',
                'raw' => $info['raw'],
            ], 422);
        }

        if (! $info['nominal'] || $info['nominal'] < 1) {
            return response()->json([
                'error' => 'Nominal tagihan tidak terdeteksi dari respons. Detail: '.mb_substr($info['raw'], 0, 200),
                'raw' => $info['raw'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'customer_name' => $info['customer_name'],
            'nominal' => $info['nominal'],
            'admin_fee' => (float) $product->admin_fee,
            'total' => round($info['nominal'] + (float) $product->admin_fee),
            'raw' => $info['raw'],
        ]);
    }
}
