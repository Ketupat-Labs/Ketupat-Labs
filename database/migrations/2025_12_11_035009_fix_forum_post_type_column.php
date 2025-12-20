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
        // First, temporarily change column to VARCHAR to allow any values
        DB::statement("ALTER TABLE forum_post MODIFY COLUMN post_type VARCHAR(50) DEFAULT 'discussion'");
        
        // Update any invalid or null values to 'discussion'
        DB::statement("UPDATE forum_post SET post_type = 'discussion' WHERE post_type NOT IN ('discussion', 'question', 'announcement') OR post_type IS NULL");
        
        // Now change it back to ENUM with the correct values
        DB::statement("ALTER TABLE forum_post MODIFY COLUMN post_type ENUM('discussion', 'question', 'announcement') DEFAULT 'discussion' NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original state if needed
        // This is a safety measure - typically you wouldn't need to reverse this
    }
};
