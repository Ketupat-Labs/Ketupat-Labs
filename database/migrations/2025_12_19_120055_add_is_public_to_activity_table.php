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
        // Check if column already exists (in case migration was run before table rename)
        if (!Schema::hasColumn('activity', 'is_public')) {
            Schema::table('activity', function (Blueprint $table) {
                $table->boolean('is_public')->default(false)->after('teacher_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('activity', 'is_public')) {
            Schema::table('activity', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }
    }
};
