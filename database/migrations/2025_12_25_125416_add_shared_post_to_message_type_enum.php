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
        // Alter the ENUM to include 'shared_post', 'link', and 'image' if not already present
        // MySQL doesn't support direct ENUM modification via Schema, so we need to use raw SQL
        DB::statement("ALTER TABLE `message` MODIFY COLUMN `message_type` ENUM('text', 'image', 'file', 'link', 'shared_post', 'system') NOT NULL DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values (remove 'shared_post' and 'link')
        // Note: This will fail if there are existing messages with 'shared_post' or 'link' type
        DB::statement("ALTER TABLE `message` MODIFY COLUMN `message_type` ENUM('text', 'image', 'file', 'system') NOT NULL DEFAULT 'text'");
    }
};
