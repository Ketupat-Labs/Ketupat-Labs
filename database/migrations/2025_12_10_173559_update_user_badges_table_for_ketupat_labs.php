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
        if (Schema::hasTable('user_badges')) {
            // Drop old columns if they exist
            Schema::table('user_badges', function (Blueprint $table) {
                if (Schema::hasColumn('user_badges', 'badge_name')) {
                    $table->dropColumn('badge_name');
                }
                if (Schema::hasColumn('user_badges', 'badge_type')) {
                    $table->dropColumn('badge_type');
                }
                if (Schema::hasColumn('user_badges', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('user_badges', 'icon_url')) {
                    $table->dropColumn('icon_url');
                }
                if (Schema::hasColumn('user_badges', 'earned_at')) {
                    $table->dropColumn('earned_at');
                }
            });
            
            // Add badge_code column if it doesn't exist
            Schema::table('user_badges', function (Blueprint $table) {
                if (!Schema::hasColumn('user_badges', 'badge_code')) {
                    $table->string('badge_code')->after('user_id');
                    $table->foreign('badge_code')->references('code')->on('badges')->onDelete('cascade');
                    $table->index('badge_code');
                }
            });
        } else {
            // Create table if it doesn't exist
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
                $table->string('badge_code');
                $table->timestamps();
                
                $table->foreign('badge_code')->references('code')->on('badges')->onDelete('cascade');
                $table->unique(['user_id', 'badge_code']);
                $table->index('user_id');
                $table->index('badge_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally restore old structure, but we'll keep the new one
        // This is a one-way migration to the new structure
    }
};
