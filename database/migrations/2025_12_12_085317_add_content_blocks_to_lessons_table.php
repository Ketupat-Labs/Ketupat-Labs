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
        Schema::table('lesson', function (Blueprint $table) {
            // Add JSON column for block-based content after the existing content column
            $table->json('content_blocks')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }
};
