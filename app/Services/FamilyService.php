<?php

namespace App\Services;

use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FamilyService
{
    /**
     * Buat keluarga baru + set user sebagai admin_keluarga + buat wallet default.
     */
    public function createFamily(User $user, string $familyName): Family
    {
        return DB::transaction(function () use ($user, $familyName) {
            // 1. Buat keluarga
            $code = strtoupper(Str::random(6));
            while (Family::where('invite_code', $code)->exists()) {
                $code = strtoupper(Str::random(6));
            }

            $family = Family::create([
                'name' => $familyName,
                'invite_code' => $code,
            ]);

            // 2. Set user sebagai admin keluarga
            $user->update([
                'family_id' => $family->id,
                'role' => 'admin_keluarga',
            ]);

            // 3. Buat wallet default "Dompet Tunai"
            $wallet = Wallet::withoutGlobalScopes()->create([
                'family_id' => $family->id,
                'name' => 'Dompet Tunai',
                'type' => 'cash',
                'balance' => 0,
                'color' => '#10b981',
                'icon' => 'wallet',
                'is_active' => true,
            ]);

            // 4. Set wallet default untuk user
            $user->update(['default_wallet_id' => $wallet->id]);

            return $family;
        });
    }

    /**
     * Kirim undangan ke email.
     */
    public function sendInvitation(Family $family, string $email, string $role = 'anggota'): FamilyInvitation
    {
        // Cek apakah user sudah bergabung di keluarga ini
        $existingUser = User::where('email', $email)
            ->where('family_id', $family->id)
            ->first();

        if ($existingUser) {
            throw new \Exception('User sudah menjadi anggota keluarga ini.');
        }

        // Hapus undangan lama yang belum diterima untuk email yang sama
        FamilyInvitation::where('family_id', $family->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        // Buat undangan baru
        return FamilyInvitation::create([
            'family_id' => $family->id,
            'email' => $email,
            'token' => Str::random(64),
            'role' => $role,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Terima undangan bergabung ke keluarga.
     */
    public function acceptInvitation(User $user, string $token): Family
    {
        $invitation = FamilyInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            throw new \Exception('Undangan sudah kadaluarsa.');
        }

        if ($user->email !== $invitation->email) {
            throw new \Exception('Email Anda tidak sesuai dengan undangan ini.');
        }

        if ($user->family_id) {
            throw new \Exception('Anda sudah bergabung di keluarga lain. Keluar dulu sebelum bergabung.');
        }

        return DB::transaction(function () use ($user, $invitation) {
            // Update user
            $user->update([
                'family_id' => $invitation->family_id,
                'role' => $invitation->role,
            ]);

            // Set default wallet ke wallet pertama keluarga
            $wallet = Wallet::withoutGlobalScopes()
                ->where('family_id', $invitation->family_id)
                ->where('is_active', true)
                ->first();

            if ($wallet) {
                $user->update(['default_wallet_id' => $wallet->id]);
            }

            // Tandai undangan sebagai diterima
            $invitation->update(['accepted_at' => now()]);

            return $invitation->family;
        });
    }

    /**
     * Keluarkan anggota dari keluarga.
     */
    public function removeMember(Family $family, User $member): void
    {
        if ($member->family_id !== $family->id) {
            throw new \Exception('User bukan anggota keluarga ini.');
        }

        // Admin terakhir tidak bisa keluar
        if ($member->role === 'admin_keluarga') {
            $adminCount = User::where('family_id', $family->id)
                ->where('role', 'admin_keluarga')
                ->count();

            if ($adminCount <= 1) {
                throw new \Exception('Tidak bisa mengeluarkan admin terakhir. Jadikan anggota lain sebagai admin terlebih dahulu.');
            }
        }

        $member->update([
            'family_id' => null,
            'role' => 'anggota',
            'default_wallet_id' => null,
        ]);
    }

    /**
     * User meninggalkan keluarga.
     */
    public function leaveFamily(User $user): void
    {
        $this->removeMember($user->family, $user);
    }

    /**
     * Ubah role anggota.
     */
    public function changeRole(Family $family, User $member, string $newRole): void
    {
        if ($member->family_id !== $family->id) {
            throw new \Exception('User bukan anggota keluarga ini.');
        }

        $member->update(['role' => $newRole]);
    }
}
