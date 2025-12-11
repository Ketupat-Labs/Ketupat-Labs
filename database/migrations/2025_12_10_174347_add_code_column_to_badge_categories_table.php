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
        if (Schema::hasTable('badge_categories')) {
            Schema::table('badge_categories', function (Blueprint $table) {
                // Add code column if it doesn't exist
                if (!Schema::hasColumn('badge_categories', 'code')) {
                    $table->string('code')->nullable()->after('name');
                }
            });
            
            // If slug exists, copy values to code, then drop slug
            if (Schema::hasColumn('badge_categories', 'slug')) {
                DB::statement('UPDATE badge_categories SET code = slug WHERE code IS NULL');
                Schema::table('badge_categories', function (Blueprint $table) {
                    $table->dropColumn('slug');
                });
            }
            
            // Make code unique and not null if it exists
            if (Schema::hasColumn('badge_categories', 'code')) {
                Schema::table('badge_categories', function (Blueprint $table) {
                    $table->string('code')->nullable(false)->unique()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('badge_categories')) {
            Schema::table('badge_categories', function (Blueprint $table) {
                if (Schema::hasColumn('badge_categories', 'code')) {
                    $table->dropColumn('code');
                }
                if (!Schema::hasColumn('badge_categories', 'slug')) {
                    $table->string('slug')->unique()->after('name');
                }
            });
        }
    }
};
