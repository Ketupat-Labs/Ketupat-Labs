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
        Schema::table('post_attachment', function (Blueprint $table) {
            $table->enum('attachment_type', ['post', 'link'])->nullable()->after('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_attachment', function (Blueprint $table) {
            $table->dropColumn('attachment_type');
        });
    }
};
