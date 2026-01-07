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
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Add activity_id to link badge to a specific activity (1:1 relationship)
            if (!Schema::hasColumn('badge', 'activity_id')) {
                $table->unsignedBigInteger('activity_id')->nullable()->unique()->after('creator_id');
                $table->foreign('activity_id')->references('id')->on('activity')->onDelete('set null');
            }
            
            // Add is_custom flag to distinguish teacher-created badges from system badges
            if (!Schema::hasColumn('badge', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('activity_id');
            }
        });
        
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
