<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('phone')->nullable()->after('avatar');
            $table->foreignId('family_id')->nullable()->after('phone')->constrained()->nullOnDelete();
            $table->enum('role', ['admin_keluarga', 'anggota'])->default('anggota')->after('family_id');
            $table->string('telegram_chat_id')->nullable()->unique()->after('role');
            $table->foreignId('default_wallet_id')->nullable()->after('telegram_chat_id')->constrained('wallets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropForeign(['default_wallet_id']);
            $table->dropColumn([
                'google_id',
                'avatar',
                'phone',
                'family_id',
                'role',
                'telegram_chat_id',
                'default_wallet_id',
            ]);
        });
    }
};
