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
        // Step 1: Create a temporary index to satisfy MySQL's Foreign Key requirement for family_id
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('family_id', 'temp_family_id_idx');
        });

        // Step 2: Perform structural changes and swap unique indexes
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

        // Step 3: Drop the temporary index as the new unique composite index starts with family_id and covers the foreign key
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('temp_family_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Create temporary index to support foreign key
        Schema::table('budgets', function (Blueprint $table) {
            $table->index('family_id', 'temp_family_id_idx');
        });

        // Step 2: Revert back to original unique index
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique('budgets_unique_period');
            $table->dropColumn(['period', 'week', 'is_recurring', 'note']);
            $table->tinyInteger('month')->nullable(false)->change();
            $table->smallInteger('year')->nullable(false)->change();
            $table->unique(['family_id', 'category_id', 'month', 'year']);
        });

        // Step 3: Drop temporary index
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('temp_family_id_idx');
        });
    }
};
