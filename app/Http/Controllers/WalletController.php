<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Store a new wallet.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'type' => 'required|in:bank,cash,ewallet,investment',
            'balance' => 'required|numeric|min:0',
            'color' => 'required|string|max:7',
        ]);

        $family = $request->user()->family;

        Wallet::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['balance'],
            'color' => $validated['color'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Dompet baru berhasil ditambahkan!');
    }

    /**
     * Delete a wallet.
     */
    public function destroy(Request $request, Wallet $wallet)
    {
        $family = $request->user()->family;

        if ($wallet->family_id !== $family->id) {
            abort(403);
        }

        // Keep at least one active wallet
        $activeCount = Wallet::where('family_id', $family->id)->where('is_active', true)->count();
        if ($activeCount <= 1) {
            return back()->withErrors(['wallet' => 'Keluarga Anda harus memiliki minimal satu dompet aktif.']);
        }

        $wallet->delete();

        return back()->with('success', 'Dompet berhasil dihapus!');
    }
}
