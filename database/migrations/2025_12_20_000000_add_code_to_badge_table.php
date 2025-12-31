<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists (it should, as 'badge')
        if (Schema::hasTable('badge')) {
            Schema::table('badge', function (Blueprint $table) {
                if (!Schema::hasColumn('badge', 'code')) {
                    $table->string('code')->nullable()->after('id');
                    // We make it nullable first to avoid issues with existing rows, 
                    // or we could fill it. For now, let's just add it. 
                    // Ideally it should be unique, but let's handle that carefully.
                }

                if (!Schema::hasColumn('badge', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('description');
                    // Check if badge_category table exists for FK
                    if (Schema::hasTable('badge_category')) {
                        $table->foreign('category_id')->references('id')->on('badge_category')->onDelete('set null');
                    } elseif (Schema::hasTable('badge_categories')) {
                        $table->foreign('category_id')->references('id')->on('badge_categories')->onDelete('set null');
                    }
                }

                if (!Schema::hasColumn('badge', 'xp_reward')) {
                    $table->integer('xp_reward')->default(0)->after('category_id');
                }
            });

            // Populate code if empty? 
            // For existing badges, we might need a code. 
            // Let's update existing rows with a slugified name as code if needed.
            // DB::statement("UPDATE badge SET code = LOWER(REPLACE(name, ' ', '-')) WHERE code IS NULL");
            // Then make it unique?
            // Schema::table('badge', function (Blueprint $table) {
            //      $table->unique('code');
            // });
            // For now, let's just ensure the column exists.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('badge')) {
            Schema::table('badge', function (Blueprint $table) {
                if (Schema::hasColumn('badge', 'code')) {
                    // Drop index if exists?
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('badge', 'category_id')) {
                    $table->dropForeign(['category_id']);
                    $table->dropColumn('category_id');
                }
                if (Schema::hasColumn('badge', 'xp_reward')) {
                    $table->dropColumn('xp_reward');
                }
            });
        }
    }
};
