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
        if (Schema::hasTable('user')) {
        Schema::table('user', function (Blueprint $table) {
                if (!Schema::hasColumn('user', 'share_badges_on_profile')) {
                    $table->boolean('share_badges_on_profile')->default(true)->after('allow_friend_requests');
                }
                if (!Schema::hasColumn('user', 'visible_badge_codes')) {
                    $table->json('visible_badge_codes')->nullable()->after('share_badges_on_profile');
                }
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user')) {
        Schema::table('user', function (Blueprint $table) {
                if (Schema::hasColumn('user', 'share_badges_on_profile')) {
                    $table->dropColumn('share_badges_on_profile');
                }
                if (Schema::hasColumn('user', 'visible_badge_codes')) {
                    $table->dropColumn('visible_badge_codes');
                }
        });
        }
    }
};
