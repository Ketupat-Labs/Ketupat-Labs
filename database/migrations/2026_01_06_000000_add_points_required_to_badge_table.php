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
        Schema::table('badge', function (Blueprint $table) {
            if (!Schema::hasColumn('badge', 'points_required')) {
                $table->integer('points_required')->default(0)->after('requirement_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge', function (Blueprint $table) {
            if (Schema::hasColumn('badge', 'points_required')) {
                $table->dropColumn('points_required');
            }
        });
    }
};
