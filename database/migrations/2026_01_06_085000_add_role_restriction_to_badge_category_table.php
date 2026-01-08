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
        if (!Schema::hasColumn('badge_category', 'role_restriction')) {
            Schema::table('badge_category', function (Blueprint $table) {
                $table->string('role_restriction')->default('all')->after('description'); // 'student', 'teacher', 'all'
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('badge_category', 'role_restriction')) {
            Schema::table('badge_category', function (Blueprint $table) {
                $table->dropColumn('role_restriction');
            });
        }
    }
};
