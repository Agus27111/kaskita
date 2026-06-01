<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\VoiceParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Store a new transaction (income, expense, or transfer).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $family = $user->family;
        $familyId = $family->id;

        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'wallet_id' => [
                'required',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query
                    ->where('family_id', $familyId)
                    ->where('is_active', true)),
            ],
            'category_id' => [
                'exclude_if:type,transfer',
                'required_unless:type,transfer',
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('family_id', $familyId)
                    ->where('type', $request->input('type'))),
            ],
            'transfer_to_wallet_id' => [
                'required_if:type,transfer',
                'nullable',
                'different:wallet_id',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query
                    ->where('family_id', $familyId)
                    ->where('is_active', true)),
            ],
            'note' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $msg = DB::transaction(function () use ($validated, $family, $user) {
            $wallet = Wallet::where('family_id', $family->id)->findOrFail($validated['wallet_id']);
            $targetWallet = null;

            if ($validated['type'] === 'transfer') {
                $targetWallet = Wallet::where('family_id', $family->id)
                    ->findOrFail($validated['transfer_to_wallet_id']);
            }

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

            $formattedAmount = 'Rp '.number_format((float) $validated['amount'], 0, ',', '.');
            if ($validated['type'] === 'income') {
                $message = "💵 {$user->name} baru saja mencatat Pemasukan sebesar {$formattedAmount} ke dompet {$wallet->name}!";
            } elseif ($validated['type'] === 'expense') {
                $message = "💸 {$user->name} baru saja mencatat Pengeluaran sebesar {$formattedAmount} dari dompet {$wallet->name}!";
            } else {
                $message = "🔄 {$user->name} baru saja melakukan Transfer sebesar {$formattedAmount} dari {$wallet->name} ke {$targetWallet->name}!";
            }

            NotificationLog::create([
                'family_id' => $family->id,
                'user_id' => $user->id,
                'channel' => 'system',
                'recipient' => 'family',
                'message' => $message,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return $message;
        });

        return back()->with('success', $msg);
    }

    /**
     * Parse voice input text into structured transaction data.
     */
    public function parseVoice(Request $request, VoiceParserService $service)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:500',
        ]);

        // Family scope is automatically applied by Category global scope
        $categories = Category::get(['id', 'name', 'type'])->toArray();

        $result = $service->parse($validated['text'], $categories);

        return response()->json($result);
    }

    /**
     * Delete a transaction from the family activity history.
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        if ((int) $transaction->family_id !== (int) $request->user()->family_id) {
            abort(403);
        }

        $transaction->delete();

        return back()->with('success', 'Riwayat transaksi berhasil dihapus.');
    }
}
