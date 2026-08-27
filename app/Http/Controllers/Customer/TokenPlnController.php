<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PendingInquiry;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Halaman Token PLN — sistemnya hampir sama dengan menu Pulsa,
 * tapi khusus menampilkan produk dari kategori "Token PLN".
 * Tidak ada deteksi operator; user memasukkan nomor meter / ID pelanggan.
 */
class TokenPlnController extends Controller
{
    public function index()
    {
        $category = Category::where('name', 'Token PLN')->first();

        return view('customer.layanan.index', [
            'scope' => 'pln',
            'category' => $category,
            'operators' => array_keys(PulsaController::OPERATOR_PREFIXES),
        ]);
    }

    /**
     * AJAX: Kirim inquiry cek nama ID pelanggan PLN ke OkeConnect.
     * Gratis — tidak memotong saldo.
     *
     * CPLN adalah produk inquiry 2-step:
     * 1. Request pertama → "akan diproses" (pending)
     * 2. checkStatus dengan refID → "sudah pernah, status Sukses. SN: IDPEL/NAMA/ALAMAT"
     */
    public function cekId(Request $request, OkeConnectService $okeconnect)
    {
        $destination = preg_replace('/\D/', '', (string) $request->input('destination', ''));

        if (strlen($destination) < 8) {
            return response()->json(['error' => 'Nomor meter / ID pelanggan tidak valid.'], 422);
        }

        if (! $okeconnect->isConfigured()) {
            return response()->json(['error' => 'Layanan cek ID pelanggan sedang dinonaktifkan sementara. Hubungi admin.'], 422);
        }

        // Cek apakah ada inquiry pending untuk nomor ini (dalam 60 detik terakhir)
        $recentPending = PendingInquiry::where('destination', $destination)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->first();

        if ($recentPending) {
            return response()->json([
                'pending' => true,
                'ref_id' => $recentPending->ref_id,
                'message' => 'Sedang mengecek... Silakan tunggu sebentar.',
            ]);
        }

        $refId = 'CKPLN'.date('ymdHis').strtoupper(Str::random(4));

        // Step 1: Kirim inquiry ke OkeConnect
        // CATATAN: transact() SUDAH memanggil parse() di dalamnya,
        // jadi tidak perlu memanggil parse() lagi.
        $info = $okeconnect->transact('CPLN', $destination, $refId);

        // Jika langsung success → ambil nama dari SN
        if ($info['status'] === 'success') {
            $fullSn = $this->extractFullSn($info['raw'] ?? '');
            $name = $this->extractNameFromSn($fullSn);

            return response()->json([
                'success' => true,
                'customer_name' => $name ?: 'Nama tidak ditemukan',
                'raw' => $info['raw'],
            ]);
        }

        if ($info['status'] === 'nodata') {
            return response()->json([
                'error' => 'ID pelanggan tidak ditemukan. Pastikan nomor sudah benar.',
                'raw' => $info['raw'],
            ], 422);
        }

        // Pending → simpan & frontend akan poll
        PendingInquiry::create([
            'ref_id' => $refId,
            'product_code' => 'CPLN',
            'destination' => $destination,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'raw' => $info['raw'],
        ]);

        return response()->json([
            'pending' => true,
            'ref_id' => $refId,
            'message' => 'Sedang mengecek... Silakan tunggu sebentar.',
        ]);
    }

    /**
     * AJAX: Poll hasil inquiry cek ID pelanggan.
     * Frontend memanggil endpoint ini berulang sampai dapat hasil.
     *
     * CPLN checkStatus mengembalikan SN dalam format:
     *   IDPEL/NAMA_PELANGGAN/ALAMAT
     * Contoh: 532652214873/JB17 TETEN/R1/450 VA
     */
    public function cekIdResult(Request $request, OkeConnectService $okeconnect)
    {
        $refId = $request->input('ref_id');

        if (! $refId) {
            return response()->json(['error' => 'ref_id wajib diisi.'], 422);
        }

        $inquiry = PendingInquiry::where('ref_id', $refId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $inquiry) {
            return response()->json(['error' => 'Inquiry tidak ditemukan.'], 404);
        }

        if ($inquiry->status === 'success') {
            return response()->json([
                'success' => true,
                'customer_name' => $inquiry->customer_name,
                'raw' => $inquiry->raw,
            ]);
        }

        if ($inquiry->status === 'failed') {
            return response()->json([
                'error' => $inquiry->raw ?: 'Cek ID pelanggan gagal.',
                'raw' => $inquiry->raw,
            ], 422);
        }

        // Masih pending → coba checkStatus ke OkeConnect
        // CATATAN: checkStatus() SUDAH memanggil parse() di dalamnya
        if ($okeconnect->isConfigured()) {
            // PENTING: checkStatus harus pakai refID (bukan trxID)
            $checkResult = $okeconnect->checkStatus('CPLN', $inquiry->destination, $inquiry->ref_id);

            if ($checkResult['status'] === 'success') {
                $fullSn = $this->extractFullSn($checkResult['raw'] ?? '');
                $parts = explode('/', (string) $fullSn);
                $name = count($parts) >= 3 ? trim($parts[1]) : null;
                // Daya: gabungkan R1 + 450 VA → "R1/450 VA"
                $daya = count($parts) >= 4 ? trim($parts[2]).'/'.trim($parts[3]) : (count($parts) >= 3 ? trim($parts[2]) : null);

                $inquiry->update([
                    'status' => 'success',
                    'customer_name' => $name ?: 'Nama tidak ditemukan',
                    'raw' => $checkResult['raw'],
                ]);

                return response()->json([
                    'success' => true,
                    'customer_name' => $name ?: 'Nama tidak ditemukan',
                    'daya' => $daya,
                    'raw' => $checkResult['raw'],
                ]);
            }

            if ($checkResult['status'] === 'failed') {
                $inquiry->update([
                    'status' => 'failed',
                    'raw' => $checkResult['raw'],
                ]);

                return response()->json([
                    'error' => $checkResult['reason'] ?: 'Cek ID pelanggan gagal.',
                    'raw' => $checkResult['raw'],
                ], 422);
            }
        }

        // Timeout: jika sudah lebih dari 20 detik, mark sebagai failed
        if ($inquiry->created_at->diffInSeconds(now()) > 20) {
            $inquiry->update(['status' => 'failed']);

            return response()->json([
                'error' => 'Timeout — server belum merespon. Silakan coba lagi.',
                'raw' => $inquiry->raw,
            ], 422);
        }

        return response()->json(['pending' => true]);
    }

    /**
     * Extract full SN dari raw response OkeConnect.
     * Regex parse di service hanya capture [A-Z0-9.\-], tidak capture '/'.
     * Kita extract manual: everything setelah "SN:" sampai sebelum "." atau "Hrg"
     * Contoh raw: ...SN: 532652214873/JB17 TETEN/R1/450 VA. Hrg...
     */
    private function extractFullSn(string $raw): ?string
    {
        if (preg_match('/SN:\s*(.+?)(?:\.\s*Hrg|\.\s*Saldo|$)/i', $raw, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Extract nama pelanggan dari SN OkeConnect.
     * Format SN: IDPEL/NAMA/ALAMAT
     * Contoh: 532652214873/JB17 TETEN/R1/450 VA
     */
    private function extractNameFromSn(?string $sn): ?string
    {
        if (! $sn) {
            return null;
        }

        $parts = explode('/', $sn);

        // Format: IDPEL/NAMA/ALAMAT → ambil bagian kedua
        if (count($parts) >= 3) {
            return trim($parts[1]);
        }

        // Fallback: jika hanya 2 bagian, ambil bagian kedua
        if (count($parts) === 2) {
            return trim($parts[1]);
        }

        return null;
    }
}
