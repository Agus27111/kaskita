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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['family_id', 'category_id', 'type', 'date'], 'transactions_family_category_type_date_idx');
            $table->index(['family_id', 'user_id', 'date'], 'transactions_family_user_date_idx');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['family_id', 'period', 'year', 'month'], 'budgets_family_period_month_idx');
            $table->index(['family_id', 'period', 'year', 'week'], 'budgets_family_period_week_idx');
        });

        Schema::table('wallet_types', function (Blueprint $table) {
            $table->index(['family_id', 'name'], 'wallet_types_family_name_idx');
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            $table->index(['family_id', 'status', 'sent_at'], 'notification_logs_family_status_sent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_family_category_type_date_idx');
            $table->dropIndex('transactions_family_user_date_idx');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('budgets_family_period_month_idx');
            $table->dropIndex('budgets_family_period_week_idx');
        });

        Schema::table('wallet_types', function (Blueprint $table) {
            $table->dropIndex('wallet_types_family_name_idx');
        });

        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex('notification_logs_family_status_sent_idx');
        });
    }
};
