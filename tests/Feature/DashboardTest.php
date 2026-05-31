<?php

use App\Models\Family;
use App\Models\User;
use App\Models\Wallet;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users without a family are redirected to setup', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('family.setup'));
});

test('authenticated users with a family can visit the dashboard', function () {
    $family = Family::create(['name' => 'Test Family']);
    $user = User::factory()->create(['family_id' => $family->id]);

    Wallet::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'name' => 'Dompet Tunai',
        'type' => 'Uang Tunai (Cash)',
        'balance' => 0,
        'color' => '#10b981',
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
