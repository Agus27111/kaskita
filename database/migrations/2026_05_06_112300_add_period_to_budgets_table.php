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
        Schema::table('budgets', function (Blueprint $table) {
            // Add period type: monthly or weekly
            $table->enum('period', ['monthly', 'weekly'])->default('monthly')->after('amount');

            // Allow null month/year for weekly budgets that repeat
            $table->tinyInteger('month')->nullable()->change();
            $table->smallInteger('year')->nullable()->change();

            // Week number for weekly budgets
            $table->tinyInteger('week')->nullable()->after('period');

            // Whether this budget auto-renews each period
            $table->boolean('is_recurring')->default(true)->after('year');

            // Optional notes
            $table->string('note')->nullable()->after('is_recurring');

            // Drop the old unique and add a new one
            $table->dropUnique(['family_id', 'category_id', 'month', 'year']);
            $table->unique(['family_id', 'category_id', 'period', 'month', 'year', 'week'], 'budgets_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique('budgets_unique_period');
            $table->dropColumn(['period', 'week', 'is_recurring', 'note']);
            $table->tinyInteger('month')->nullable(false)->change();
            $table->smallInteger('year')->nullable(false)->change();
            $table->unique(['family_id', 'category_id', 'month', 'year']);
        });
    }
};
