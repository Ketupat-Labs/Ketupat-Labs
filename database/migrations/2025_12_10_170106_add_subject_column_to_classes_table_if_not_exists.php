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
        if (!Schema::hasColumn('classes', 'subject')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('subject', 200)->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('classes', 'subject')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('subject');
            });
        }
    }
};
