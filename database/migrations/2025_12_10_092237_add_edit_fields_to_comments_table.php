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
        Schema::table('comment', function (Blueprint $table) {
            if (!Schema::hasColumn('comment', 'is_edited')) {
                $table->boolean('is_edited')->default(false)->after('reaction_count');
            }
            if (!Schema::hasColumn('comment', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('is_edited');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comment', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'edited_at']);
        });
    }
};
