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
        Schema::table('activity_submission', function (Blueprint $table) {
            // Make activity_assignment_id nullable to support public activities
            $table->unsignedBigInteger('activity_assignment_id')->nullable()->change();
            
            // Add activity_id to link directly to the activity if no specific assignment exists
            $table->foreignId('activity_id')->nullable()->constrained('activity')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_submission', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_assignment_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('activity_id');
        });
    }
};
