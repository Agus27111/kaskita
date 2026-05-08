<?php

namespace App\Http\Controllers;

use App\Models\WalletType;
use Illuminate\Http\Request;

class WalletTypeController extends Controller
{
    /**
     * Store a custom wallet type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
        ]);

        $family = $request->user()->family;

        WalletType::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? '💰',
        ]);

        return back()->with('success', 'Jenis dompet kustom berhasil ditambahkan!');
    }

    /**
     * Delete a custom wallet type.
     */
    public function destroy(Request $request, WalletType $walletType)
    {
        $family = $request->user()->family;

        if ($walletType->family_id !== $family->id) {
            abort(403);
        }

        $walletType->delete();

        return back()->with('success', 'Jenis dompet kustom berhasil dihapus!');
    }
}
