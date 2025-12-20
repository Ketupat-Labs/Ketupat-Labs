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
        if (Schema::hasTable('badge')) {
            Schema::table('badge', function (Blueprint $table) {
                // 1. Ensure 'code' column exists
                if (!Schema::hasColumn('badge', 'code')) {
                    $table->string('code')->nullable()->unique()->after('id');
                }

                // 2. Fix category column
                if (Schema::hasColumn('badge', 'category_slug') && !Schema::hasColumn('badge', 'category_code')) {
                    $table->renameColumn('category_slug', 'category_code');
                } elseif (!Schema::hasColumn('badge', 'category_code')) {
                    $table->string('category_code')->nullable()->after('description');
                }
                
                // 3. Add points_required if missing (rename requirement_value if exists?)
                if (Schema::hasColumn('badge', 'requirement_value') && !Schema::hasColumn('badge', 'points_required')) {
                    $table->renameColumn('requirement_value', 'points_required');
                } elseif (!Schema::hasColumn('badge', 'points_required')) {
                    $table->integer('points_required')->default(100)->after('requirement_type');
                }

                // 4. Add xp_reward
                if (!Schema::hasColumn('badge', 'xp_reward')) {
                    $table->integer('xp_reward')->default(0)->after('points_required');
                }
                
                // 5. Ensure name_bm exists
                if (!Schema::hasColumn('badge', 'name_bm')) {
                    $table->string('name_bm')->nullable()->after('name');
                }

                // 6. Ensure category_id exists (critical for linking)
                if (!Schema::hasColumn('badge', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('category_code');
                    // We won't strictly enforce FK here to avoid failures if table doesn't exist yet, 
                    // but usually badge_category exists by now.
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No simple reverse for this fix-up migration
    }
};
