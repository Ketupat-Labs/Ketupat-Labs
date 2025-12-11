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
        Schema::table('user', function (Blueprint $table) {
            if (!Schema::hasColumn('user', 'school')) {
                $table->string('school')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('user', 'class')) {
                $table->string('class')->nullable()->after('school');
            }
            if (!Schema::hasColumn('user', 'bio')) {
                $table->text('bio')->nullable()->after('class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'school')) {
                $table->dropColumn('school');
            }
            if (Schema::hasColumn('user', 'class')) {
                $table->dropColumn('class');
            }
            if (Schema::hasColumn('user', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};
