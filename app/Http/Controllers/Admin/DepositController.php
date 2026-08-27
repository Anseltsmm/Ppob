<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('q');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Deposit::with('user');

        if (in_array($status, ['UNPAID', 'PAID', 'EXPIRED', 'FAILED'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $deposits = $query->latest()->paginate(20)->withQueryString();

        $base = Deposit::query();
        $counts = [
            'all' => (clone $base)->count(),
            'PAID' => (clone $base)->where('status', 'PAID')->count(),
            'UNPAID' => (clone $base)->where('status', 'UNPAID')->count(),
            'EXPIRED' => (clone $base)->where('status', 'EXPIRED')->count(),
        ];

        return view('admin.deposits.index', compact('deposits', 'status', 'search', 'counts', 'dateFrom', 'dateTo'));
    }

    public function show(Deposit $deposit)
    {
        return view('admin.deposits.show', compact('deposit'));
    }
}
