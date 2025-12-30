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
        if (Schema::hasTable('user') && !Schema::hasColumn('user', 'first_login_at')) {
            Schema::table('user', function (Blueprint $table) {
                $table->timestamp('first_login_at')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user') && Schema::hasColumn('user', 'first_login_at')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('first_login_at');
            });
        }
    }
};
