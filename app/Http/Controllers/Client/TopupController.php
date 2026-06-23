<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopupController extends Controller
{
    public function index()
    {
        return view('client.topup.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
            'payment_method' => 'required|string|in:gopay,ovo,bca,mandiri,bri',
        ]);

        return redirect()->route('client.topup.payment', [
            'amount' => $request->amount,
            'method' => $request->payment_method,
        ]);
    }

    public function payment(Request $request)
    {
        $amount = $request->query('amount');
        $method = $request->query('method');

        if (!$amount || !$method) {
            return redirect()->route('client.topup.index')->with('error', 'Detail top-up tidak valid.');
        }

        return view('client.topup.payment', compact('amount', 'method'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'method' => 'required|string',
        ]);

        $amount = $request->amount;
        $method = $request->method;

        try {
            DB::transaction(function () use ($amount, $method) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => auth()->id()],
                    ['balance' => 0.00]
                );

                // Lock for update
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

                $balanceBefore = $wallet->balance;
                
                // Kredit saldo ke wallet
                $wallet->increment('balance', $amount);

                // Buat transaksi wallet
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'booking_id' => null,
                    'type' => 'credit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->fresh()->balance,
                    'description' => 'Top Up Saldo via ' . strtoupper($method),
                ]);
            });

            return redirect()
                ->route('client.dashboard')
                ->with('success', 'Top-up sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil!');

        } catch (\Exception $e) {
            return redirect()
                ->route('client.topup.index')
                ->with('error', 'Terjadi kesalahan saat memproses top-up: ' . $e->getMessage());
        }
    }
}
