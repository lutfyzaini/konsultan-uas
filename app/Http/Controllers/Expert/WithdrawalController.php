<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $wallet = Wallet::firstOrCreate(['user_id' => auth()->id()]);
        $withdrawals = WithdrawalRequest::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('expert.withdrawals.index', compact('wallet', 'withdrawals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:150',
        ]);

        $amount = (float) $request->amount;

        try {
            DB::transaction(function () use ($amount, $request) {
                $wallet = Wallet::where('user_id', auth()->id())->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < $amount) {
                    throw new \Exception('Saldo Anda tidak mencukupi untuk melakukan penarikan.');
                }

                // Potong saldo langsung (debit) untuk memblokir dana
                $balanceBefore = $wallet->balance;
                $wallet->debit($amount);

                // Buat log transaksi wallet
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'booking_id' => null,
                    'type' => 'debit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->fresh()->balance,
                    'description' => 'Penarikan saldo (menunggu persetujuan)',
                ]);

                // Buat request penarikan
                WithdrawalRequest::create([
                    'user_id' => auth()->id(),
                    'amount' => $amount,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'status' => 'pending',
                ]);
            });

            return redirect()->route('expert.withdrawals.index')->with('success', 'Permintaan penarikan berhasil diajukan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
