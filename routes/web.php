<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTypeController;
use App\Http\Middleware\EnsureHasFamily;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Terima undangan — bisa tanpa auth (redirect ke login kalau belum)
Route::get('family/invitation/{token}', [FamilyController::class, 'acceptInvitation'])
    ->name('family.accept-invitation');

Route::middleware(['auth', 'verified'])->group(function () {
    // Family Setup — untuk user yang belum punya keluarga
    Route::get('family/setup', [FamilyController::class, 'setup'])->name('family.setup');
    Route::post('family', [FamilyController::class, 'store'])->name('family.store');
    Route::post('family/join', [FamilyController::class, 'join'])->name('family.join');

    // Semua halaman di bawah ini butuh keluarga
    Route::middleware(EnsureHasFamily::class)->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        // Budget
        Route::get('budget', [BudgetController::class, 'index'])->name('budget.index');
        Route::post('budget', [BudgetController::class, 'store'])->name('budget.store');
        Route::put('budget/{budget}', [BudgetController::class, 'update'])->name('budget.update');
        Route::delete('budget/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');

        // Activity (Aktivitas)
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('activity/download-pdf', [ActivityController::class, 'downloadPdf'])->name('activity.download-pdf');

        // Transactions (Transaksi Baru)
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::post('transactions/parse-voice', [TransactionController::class, 'parseVoice'])->name('transactions.parse-voice');

        // Wallets (Kelola Dompet)
        Route::post('wallets', [WalletController::class, 'store'])->name('wallets.store');
        Route::delete('wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');

        // Wallet Types (Kelola Jenis Dompet)
        Route::post('wallet-types', [WalletTypeController::class, 'store'])->name('wallet-types.store');
        Route::delete('wallet-types/{walletType}', [WalletTypeController::class, 'destroy'])->name('wallet-types.destroy');

        // Categories (Kelola Kategori)
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Family Settings
        Route::get('family/settings', [FamilyController::class, 'settings'])->name('family.settings');
        Route::put('family', [FamilyController::class, 'update'])->name('family.update');
        Route::post('family/invite', [FamilyController::class, 'invite'])->name('family.invite');
        Route::delete('family/invitation/{invitation}', [FamilyController::class, 'cancelInvitation'])->name('family.cancel-invitation');
        Route::delete('family/member/{member}', [FamilyController::class, 'removeMember'])->name('family.remove-member');
        Route::put('family/member/{member}/role', [FamilyController::class, 'changeRole'])->name('family.change-role');
        Route::post('family/leave', [FamilyController::class, 'leave'])->name('family.leave');
    });
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

require __DIR__.'/settings.php';
