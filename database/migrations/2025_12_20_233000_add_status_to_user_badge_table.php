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
        // This runs AFTER ensure_singular_tables (23:00:00) so user_badge exists
        if (Schema::hasTable('user_badge')) {
            Schema::table('user_badge', function (Blueprint $table) {
                if (!Schema::hasColumn('user_badge', 'status')) {
                    $table->string('status')->default('locked')->after('badge_code');
                }
                if (!Schema::hasColumn('user_badge', 'earned_at')) {
                    $table->timestamp('earned_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('user_badge', 'redeemed_at')) {
                    $table->timestamp('redeemed_at')->nullable()->after('earned_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_badge')) {
            Schema::table('user_badge', function (Blueprint $table) {
                $table->dropColumn(['status', 'earned_at', 'redeemed_at']);
            });
        }
    }
};
