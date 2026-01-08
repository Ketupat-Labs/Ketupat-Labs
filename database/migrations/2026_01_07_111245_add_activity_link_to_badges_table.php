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
        Schema::table('badge', function (Blueprint $table) {
            // Add creator_id to track which teacher created the badge
            if (!Schema::hasColumn('badge', 'creator_id')) {
                $table->unsignedBigInteger('creator_id')->nullable()->after('id');
            }
            
            // Add activity_id to link badge to a specific activity (1:1 relationship)
            if (!Schema::hasColumn('badge', 'activity_id')) {
                $table->unsignedBigInteger('activity_id')->nullable()->unique()->after('creator_id');
            }
            
            // Add is_custom flag to distinguish teacher-created badges from system badges
            if (!Schema::hasColumn('badge', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('activity_id');
            }
        });
        
        // Add foreign keys separately after columns are created
        if (Schema::hasColumn('badge', 'creator_id') && !Schema::hasColumn('badge', 'badge_creator_id_foreign')) {
            try {
                Schema::table('badge', function (Blueprint $table) {
                    $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist or users table structure doesn't match
                \Log::warning('Could not add creator_id foreign key: ' . $e->getMessage());
            }
        }
        
        if (Schema::hasColumn('badge', 'activity_id') && Schema::hasTable('activity')) {
            try {
                Schema::table('badge', function (Blueprint $table) {
                    $table->foreign('activity_id')->references('id')->on('activity')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist or activity table doesn't exist
                \Log::warning('Could not add activity_id foreign key: ' . $e->getMessage());
            }
        }
        
        // Set existing badges as system badges (not custom)
        DB::table('badge')->update(['is_custom' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['activity_id']);
            
            // Drop columns
            $table->dropColumn(['creator_id', 'activity_id', 'is_custom']);
        });
    }
};
