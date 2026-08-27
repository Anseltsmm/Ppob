<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = User::where('role', 'customer');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount('orders')->latest()->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);

        $orders = $customer->orders()->latest()->limit(10)->get();
        $histories = $customer->balanceHistories()->latest()->limit(20)->get();

        return view('admin.customers.show', compact('customer', 'orders', 'histories'));
    }

    public function adjustSaldo(Request $request, User $customer)
    {
        abort_unless($customer->role === 'customer', 404);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', 'in:credit,debit'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = abs((float) $validated['amount']);
        $description = $validated['description'] ?: 'Penyesuaian saldo oleh admin';

        if ($validated['type'] === 'debit') {
            if ((float) $customer->saldo < $amount) {
                return back()->with('error', 'Saldo customer tidak mencukupi untuk debit.');
            }
            $customer->debit($amount, $description, 'adjustment');
        } else {
            $customer->credit($amount, $description, 'adjustment');
        }

        return back()->with('success', 'Saldo customer berhasil disesuaikan.');
    }

    public function toggleStatus(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->update(['status' => ! $customer->status]);

        return back()->with('success', 'Status customer diperbarui.');
    }
}
