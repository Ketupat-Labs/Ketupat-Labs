<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. badge_categories -> badge_category
        if (Schema::hasTable('badge_categories') && !Schema::hasTable('badge_category')) {
            Schema::rename('badge_categories', 'badge_category');
        }

        // 2. badges -> badge
        if (Schema::hasTable('badges') && !Schema::hasTable('badge')) {
            Schema::rename('badges', 'badge');
        }

        // 3. user_badges -> user_badge
        if (Schema::hasTable('user_badges') && !Schema::hasTable('user_badge')) {
            Schema::rename('user_badges', 'user_badge');
        }
        
        // Ensure status column exists in user_badge (if it was just renamed from user_badges which might lack it)
        // Note: user_badges migration had status column, but checked for is_earned earlier.
        // If the table came from old migration, it has status. 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: rename back if needed, but safest to leave as is for this fix
        // We generally don't want to revert standardization
    }
};
