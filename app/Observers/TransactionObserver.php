<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->updateWalletBalance($transaction, 'increment_actual');
    }

    public function updated(Transaction $transaction): void
    {
        // Handle changes in amount, type, or wallet
        if ($transaction->wasChanged(['amount', 'type', 'wallet_id', 'transfer_to_wallet_id'])) {
            // Revert old transaction effect
            $oldTransaction = new Transaction($transaction->getOriginal());
            $this->updateWalletBalance($oldTransaction, 'decrement_actual');

            // Apply new transaction effect
            $this->updateWalletBalance($transaction, 'increment_actual');
        }
    }

    public function deleted(Transaction $transaction): void
    {
        $this->updateWalletBalance($transaction, 'decrement_actual');
    }

    protected function updateWalletBalance(Transaction $transaction, string $action): void
    {
        $amount = $transaction->amount;
        $type = $transaction->type;
        $isIncrement = ($action === 'increment_actual');

        DB::transaction(function () use ($transaction, $amount, $type, $isIncrement) {
            if ($type === 'income') {
                $this->adjustBalance($transaction->wallet_id, $isIncrement ? $amount : -$amount);
            } elseif ($type === 'expense') {
                $this->adjustBalance($transaction->wallet_id, $isIncrement ? -$amount : $amount);
            } elseif ($type === 'transfer') {
                // Source wallet
                $this->adjustBalance($transaction->wallet_id, $isIncrement ? -$amount : $amount);
                // Destination wallet
                if ($transaction->transfer_to_wallet_id) {
                    $this->adjustBalance($transaction->transfer_to_wallet_id, $isIncrement ? $amount : -$amount);
                }
            }
        });
    }

    protected function adjustBalance(int $walletId, $amount): void
    {
        Wallet::withoutGlobalScopes()
            ->whereKey($walletId)
            ->increment('balance', $amount);
    }
}
