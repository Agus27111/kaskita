<?php

use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

function createFamilyUser(): array
{
    $family = Family::create(['name' => 'Test Family']);
    $user = User::factory()->create([
        'family_id' => $family->id,
        'role' => 'admin_keluarga',
    ]);

    return [$family, $user];
}

test('creating income updates the wallet balance once', function () {
    [$family, $user] = createFamilyUser();

    $wallet = Wallet::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Bank',
        'type' => 'Rekening Bank',
        'balance' => 1000,
        'color' => '#10b981',
        'is_active' => true,
    ]);

    $category = Category::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Gaji',
        'type' => 'income',
        'icon' => 'briefcase',
        'color' => '#059669',
    ]);

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type' => 'income',
            'amount' => 500,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'note' => 'Gaji harian',
            'date' => now()->toDateString(),
        ])
        ->assertSessionHasNoErrors();

    expect((float) $wallet->refresh()->balance)->toBe(1500.0);
});

test('transfer can be created without category and moves balances once', function () {
    [$family, $user] = createFamilyUser();

    $sourceWallet = Wallet::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Bank',
        'type' => 'Rekening Bank',
        'balance' => 1000,
        'color' => '#10b981',
        'is_active' => true,
    ]);
    $targetWallet = Wallet::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Cash',
        'type' => 'Uang Tunai (Cash)',
        'balance' => 100,
        'color' => '#10b981',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type' => 'transfer',
            'amount' => 250,
            'wallet_id' => $sourceWallet->id,
            'transfer_to_wallet_id' => $targetWallet->id,
            'note' => 'Tarik tunai',
            'date' => now()->toDateString(),
        ])
        ->assertSessionHasNoErrors();

    expect((float) $sourceWallet->refresh()->balance)->toBe(750.0)
        ->and((float) $targetWallet->refresh()->balance)->toBe(350.0);

    $this->assertDatabaseHas('transactions', [
        'family_id' => $family->id,
        'wallet_id' => $sourceWallet->id,
        'transfer_to_wallet_id' => $targetWallet->id,
        'category_id' => null,
        'amount' => 250,
    ]);
});

test('deleting an expense transaction removes history and restores wallet balance', function () {
    [$family, $user] = createFamilyUser();

    $wallet = Wallet::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Bank',
        'type' => 'Rekening Bank',
        'balance' => 1000,
        'color' => '#10b981',
        'is_active' => true,
    ]);

    $category = Category::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Makanan',
        'type' => 'expense',
        'icon' => 'utensils',
        'color' => '#ef4444',
    ]);

    $transaction = Transaction::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 300,
        'note' => 'Makan siang',
        'date' => now()->toDateString(),
    ]);

    expect((float) $wallet->refresh()->balance)->toBe(700.0);

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $transaction))
        ->assertRedirect()
        ->assertSessionHas('success', 'Riwayat transaksi berhasil dihapus.');

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
    expect((float) $wallet->refresh()->balance)->toBe(1000.0);
});

test('users cannot delete transactions from another family', function () {
    [, $user] = createFamilyUser();
    $otherFamily = Family::create(['name' => 'Other Family']);
    $otherUser = User::factory()->create([
        'family_id' => $otherFamily->id,
        'role' => 'admin_keluarga',
    ]);

    $wallet = Wallet::withoutGlobalScopes()->create([
        'family_id' => $otherFamily->id,
        'name' => 'Bank Lain',
        'type' => 'Rekening Bank',
        'balance' => 500,
        'color' => '#10b981',
        'is_active' => true,
    ]);

    $category = Category::withoutGlobalScopes()->create([
        'family_id' => $otherFamily->id,
        'name' => 'Transport',
        'type' => 'expense',
        'icon' => 'car',
        'color' => '#ef4444',
    ]);

    $transaction = Transaction::withoutGlobalScopes()->create([
        'family_id' => $otherFamily->id,
        'user_id' => $otherUser->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'note' => 'Ongkos',
        'date' => now()->toDateString(),
    ]);

    expect((float) $wallet->refresh()->balance)->toBe(400.0);

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $transaction))
        ->assertForbidden();

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
    ]);
    expect((float) $wallet->refresh()->balance)->toBe(400.0);
});
