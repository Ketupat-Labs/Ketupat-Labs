<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Updates the reaction table to support:
     * - 'message' as a target_type (in addition to 'post' and 'comment')
     * - Additional reaction types: 'surprised', 'pray', 'star' (in addition to existing ones)
     */
    public function up(): void
    {
        // Modify target_type ENUM to include 'message'
        DB::statement("ALTER TABLE `reaction` MODIFY COLUMN `target_type` ENUM('post', 'comment', 'message') NOT NULL");
        
        // Modify reaction_type ENUM to include all reaction types
        DB::statement("ALTER TABLE `reaction` MODIFY COLUMN `reaction_type` ENUM('like', 'love', 'laugh', 'angry', 'sad', 'surprised', 'pray', 'star') NOT NULL DEFAULT 'like'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert target_type to original values
        DB::statement("ALTER TABLE `reaction` MODIFY COLUMN `target_type` ENUM('post', 'comment') NOT NULL");
        
        // Revert reaction_type to original values
        DB::statement("ALTER TABLE `reaction` MODIFY COLUMN `reaction_type` ENUM('like', 'love', 'laugh', 'angry', 'sad') NOT NULL DEFAULT 'like'");
    }
};
