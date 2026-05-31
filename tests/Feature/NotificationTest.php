<?php

use App\Models\Family;
use App\Models\NotificationLog;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('family system notifications are shared with inertia', function () {
    $family = Family::create(['name' => 'Test Family']);
    $user = User::factory()->create(['family_id' => $family->id]);
    $otherFamily = Family::create(['name' => 'Other Family']);
    $otherUser = User::factory()->create(['family_id' => $otherFamily->id]);

    NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'channel' => 'system',
        'recipient' => 'family',
        'message' => 'Notifikasi keluarga terbaru',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'channel' => 'email',
        'recipient' => $user->email,
        'message' => 'Notifikasi email tidak tampil',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $otherFamily->id,
        'user_id' => $otherUser->id,
        'channel' => 'system',
        'recipient' => 'family',
        'message' => 'Notifikasi keluarga lain',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.count', 1)
            ->where('notifications.items.0.message', 'Notifikasi keluarga terbaru'));
});

test('family can clear system notifications', function () {
    $family = Family::create(['name' => 'Test Family']);
    $user = User::factory()->create(['family_id' => $family->id]);
    $otherFamily = Family::create(['name' => 'Other Family']);
    $otherUser = User::factory()->create(['family_id' => $otherFamily->id]);

    $systemNotification = NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'channel' => 'system',
        'recipient' => 'family',
        'message' => 'Bersihkan saya',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $emailNotification = NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'channel' => 'email',
        'recipient' => $user->email,
        'message' => 'Log email tetap disimpan',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $otherFamilyNotification = NotificationLog::withoutGlobalScopes()->create([
        'family_id' => $otherFamily->id,
        'user_id' => $otherUser->id,
        'channel' => 'system',
        'recipient' => 'family',
        'message' => 'Notifikasi keluarga lain tetap ada',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $this->actingAs($user)
        ->delete(route('notifications.clear'))
        ->assertRedirect()
        ->assertSessionHas('success', 'Notifikasi berhasil dibersihkan.');

    $this->assertDatabaseMissing('notification_logs', [
        'id' => $systemNotification->id,
    ]);
    $this->assertDatabaseHas('notification_logs', [
        'id' => $emailNotification->id,
    ]);
    $this->assertDatabaseHas('notification_logs', [
        'id' => $otherFamilyNotification->id,
    ]);
});
