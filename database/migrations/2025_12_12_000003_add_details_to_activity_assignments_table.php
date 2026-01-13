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
        Schema::table('activity_assignment', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->after('status');
            $table->text('notes')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_assignment', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'notes']);
        });
    }
};
