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
        Schema::table('forum_post', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('is_deleted');
            $table->timestamp('hidden_at')->nullable()->after('is_hidden');
            $table->foreignId('hidden_by')->nullable()->constrained('user')->onDelete('set null')->after('hidden_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_post', function (Blueprint $table) {
            $table->dropForeign(['hidden_by']);
            $table->dropColumn(['is_hidden', 'hidden_at', 'hidden_by']);
        });
    }
};
