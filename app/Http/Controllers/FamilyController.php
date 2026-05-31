<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FamilyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FamilyController extends Controller
{
    public function __construct(private FamilyService $familyService) {}

    /**
     * Halaman setup keluarga baru (untuk user yang belum punya keluarga).
     */
    public function setup(): Response
    {
        return Inertia::render('Family/Setup');
    }

    /**
     * Proses buat keluarga baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $family = $this->familyService->createFamily(
            $request->user(),
            $validated['name']
        );

        return redirect()->route('dashboard')
            ->with('success', "Keluarga \"{$family->name}\" berhasil dibuat! 🎉");
    }

    /**
     * Halaman pengaturan keluarga (untuk admin).
     */
    public function settings(Request $request): Response
    {
        $user = $request->user();
        $family = $user->family;

        if (! $family) {
            return redirect()->route('family.setup');
        }

        $members = $family->users()
            ->select('id', 'name', 'email', 'avatar', 'role', 'created_at')
            ->get();

        $invitations = FamilyInvitation::where('family_id', $family->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Family/Settings', [
            'family' => $family->only('id', 'name', 'avatar', 'invite_code'),
            'members' => $members,
            'invitations' => $invitations,
            'isAdmin' => $user->role === 'admin_keluarga',
        ]);
    }

    /**
     * Update nama keluarga.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $family = $request->user()->family;

        if (! $family || $request->user()->role !== 'admin_keluarga') {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $family->update(['name' => $validated['name']]);

        return back()->with('success', 'Nama keluarga berhasil diperbarui.');
    }

    /**
     * Kirim undangan ke email.
     */
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin_keluarga,anggota',
        ]);

        $family = $request->user()->family;

        if (! $family || $request->user()->role !== 'admin_keluarga') {
            abort(403, 'Anda tidak memiliki akses.');
        }

        try {
            $invitation = $this->familyService->sendInvitation(
                $family,
                $validated['email'],
                $validated['role']
            );

            return back()->with('success', "Undangan berhasil dikirim ke {$validated['email']}.");
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Batalkan undangan.
     */
    public function cancelInvitation(Request $request, int $invitationId)
    {
        $family = $request->user()->family;

        if (! $family || $request->user()->role !== 'admin_keluarga') {
            abort(403);
        }

        FamilyInvitation::where('id', $invitationId)
            ->where('family_id', $family->id)
            ->whereNull('accepted_at')
            ->delete();

        return back()->with('success', 'Undangan berhasil dibatalkan.');
    }

    /**
     * Terima undangan (via token link).
     */
    public function acceptInvitation(Request $request, string $token)
    {
        $user = $request->user();

        if (! $user) {
            // Simpan token di session, redirect ke login dulu
            session(['invitation_token' => $token]);

            return redirect()->route('login')
                ->with('info', 'Silahkan login terlebih dahulu untuk menerima undangan.');
        }

        try {
            $family = $this->familyService->acceptInvitation($user, $token);

            return redirect()->route('dashboard')
                ->with('success', "Selamat datang di keluarga \"{$family->name}\"! 🎉");
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Keluarkan anggota dari keluarga.
     */
    public function removeMember(Request $request, int $memberId)
    {
        $family = $request->user()->family;

        if (! $family || $request->user()->role !== 'admin_keluarga') {
            abort(403);
        }

        $member = User::findOrFail($memberId);

        // Admin tidak bisa mengeluarkan dirinya sendiri
        if ($member->id === $request->user()->id) {
            return back()->withErrors(['member' => 'Tidak bisa mengeluarkan diri sendiri. Gunakan fitur "Tinggalkan Keluarga".']);
        }

        try {
            $this->familyService->removeMember($family, $member);

            return back()->with('success', "{$member->name} telah dikeluarkan dari keluarga.");
        } catch (\Exception $e) {
            return back()->withErrors(['member' => $e->getMessage()]);
        }
    }

    /**
     * User meninggalkan keluarga.
     */
    public function leave(Request $request)
    {
        $user = $request->user();

        if (! $user->family_id) {
            return back()->with('error', 'Anda belum bergabung di keluarga manapun.');
        }

        try {
            $this->familyService->leaveFamily($user);

            return redirect()->route('family.setup')
                ->with('success', 'Anda telah keluar dari keluarga.');
        } catch (\Exception $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    /**
     * Ubah role anggota.
     */
    public function changeRole(Request $request, int $memberId)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin_keluarga,anggota',
        ]);

        $family = $request->user()->family;

        if (! $family || $request->user()->role !== 'admin_keluarga') {
            abort(403);
        }

        $member = User::findOrFail($memberId);

        try {
            $this->familyService->changeRole($family, $member, $validated['role']);

            return back()->with('success', "Role {$member->name} berhasil diubah.");
        } catch (\Exception $e) {
            return back()->withErrors(['role' => $e->getMessage()]);
        }
    }

    /**
     * Bergabung ke keluarga lama menggunakan 6-digit invite_code keluarga.
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:10',
        ]);

        $user = $request->user();

        if ($user->family_id) {
            return back()->withErrors(['token' => 'Anda sudah bergabung di keluarga lain. Keluar dulu sebelum bergabung.']);
        }

        // Cari keluarga berdasarkan 6-digit invite_code
        $family = Family::where('invite_code', strtoupper($validated['token']))->first();

        if (! $family) {
            return back()->withErrors(['token' => 'Kode token keluarga tidak valid. Silahkan periksa kembali.']);
        }

        try {
            DB::transaction(function () use ($user, $family) {
                $user->update([
                    'family_id' => $family->id,
                    'role' => 'anggota',
                ]);

                // Set default wallet ke wallet pertama keluarga
                $wallet = Wallet::withoutGlobalScopes()
                    ->where('family_id', $family->id)
                    ->where('is_active', true)
                    ->first();

                if ($wallet) {
                    $user->update(['default_wallet_id' => $wallet->id]);
                }
            });

            return redirect()->route('dashboard')
                ->with('success', "Selamat bergabung di keluarga \"{$family->name}\"! 🎉");
        } catch (\Exception $e) {
            return back()->withErrors(['token' => $e->getMessage()]);
        }
    }
}
