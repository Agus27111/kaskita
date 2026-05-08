<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Store a new transaction (income, expense, or transfer).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:1',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'nullable|exists:categories,id',
            'transfer_to_wallet_id' => 'required_if:type,transfer|nullable|exists:wallets,id',
            'note' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $user = $request->user();
        $family = $user->family;

        $wallet = Wallet::where('family_id', $family->id)->findOrFail($validated['wallet_id']);

        // Prevent transfer to the same wallet
        if ($validated['type'] === 'transfer' && $validated['wallet_id'] == $validated['transfer_to_wallet_id']) {
            return back()->withErrors(['transfer_to_wallet_id' => 'Dompet asal dan dompet tujuan tidak boleh sama.']);
        }

        // Adjust wallet balances
        if ($validated['type'] === 'income') {
            $wallet->increment('balance', $validated['amount']);
        } elseif ($validated['type'] === 'expense') {
            $wallet->decrement('balance', $validated['amount']);
        } elseif ($validated['type'] === 'transfer') {
            $targetWallet = Wallet::where('family_id', $family->id)->findOrFail($validated['transfer_to_wallet_id']);
            $wallet->decrement('balance', $validated['amount']);
            $targetWallet->increment('balance', $validated['amount']);
        }

        // Create the transaction
        Transaction::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'wallet_id' => $validated['wallet_id'],
            'category_id' => $validated['category_id'] ?? null,
            'transfer_to_wallet_id' => $validated['transfer_to_wallet_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'date' => $validated['date'],
        ]);

        // Construct dynamic notification message
        $formattedAmount = "Rp " . number_format($validated['amount'], 0, ',', '.');
        if ($validated['type'] === 'income') {
            $msg = "💵 {$user->name} baru saja mencatat Pemasukan sebesar {$formattedAmount} ke dompet {$wallet->name}!";
        } elseif ($validated['type'] === 'expense') {
            $msg = "💸 {$user->name} baru saja mencatat Pengeluaran sebesar {$formattedAmount} dari dompet {$wallet->name}!";
        } else {
            $msg = "🔄 {$user->name} baru saja melakukan Transfer sebesar {$formattedAmount} dari {$wallet->name} ke {$targetWallet->name}!";
        }

        // Save to Notification Logs for history
        \App\Models\NotificationLog::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'channel' => 'system',
            'recipient' => 'family',
            'message' => $msg,
            'status' => 'unread',
            'sent_at' => now(),
        ]);

        return back()->with('success', $msg);
    }
}
