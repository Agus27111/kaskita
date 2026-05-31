<?php

use App\Models\Category;
use App\Models\Family;
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
