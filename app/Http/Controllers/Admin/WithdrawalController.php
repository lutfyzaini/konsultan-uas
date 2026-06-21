<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = WithdrawalRequest::with('user.profile')
            ->latest()
            ->paginate(15);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Request $request, int $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $request->validate([
            'receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $request) {
                // Upload receipt
                $path = $request->file('receipt')->store('receipts', 'public');

                $withdrawal->update([
                    'status' => 'completed',
                    'receipt_path' => $path,
                ]);
            });

            return redirect()->route('admin.withdrawals.index')->with('success', 'Permintaan penarikan berhasil disetujui.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $request) {
                $withdrawal->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->admin_notes,
                ]);

                // Kembalikan saldo ke dompet expert (refund)
                $wallet = Wallet::where('user_id', $withdrawal->user_id)->lockForUpdate()->first();
                if ($wallet) {
                    $balanceBefore = $wallet->balance;
                    
                    // Kembalikan saldo dengan credit
                    $wallet->increment('balance', $withdrawal->amount);
                    $wallet->decrement('total_withdrawn', $withdrawal->amount);

                    // Catat refund transaction log
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'booking_id' => null,
                        'type' => 'credit',
                        'amount' => $withdrawal->amount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->fresh()->balance,
                        'description' => 'Refund penarikan ditolak: ' . $request->admin_notes,
                    ]);
                }
            });

            return redirect()->route('admin.withdrawals.index')->with('success', 'Permintaan penarikan ditolak dan saldo dikembalikan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
