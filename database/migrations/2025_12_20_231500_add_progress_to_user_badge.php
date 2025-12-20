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
        if (Schema::hasTable('user_badge') && !Schema::hasColumn('user_badge', 'progress')) {
            Schema::table('user_badge', function (Blueprint $table) {
                $table->integer('progress')->default(0)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_badge') && Schema::hasColumn('user_badge', 'progress')) {
            Schema::table('user_badge', function (Blueprint $table) {
                $table->dropColumn('progress');
            });
        }
    }
};
