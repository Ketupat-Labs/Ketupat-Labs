<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('activity')) {
            if (!Schema::hasColumn('activity', 'content')) {
                Schema::table('activity', function (Blueprint $table) {
                    $table->json('content')->nullable()->after('description');
                });
            }
        }
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('activity', 'content')) {
            Schema::table('activity', function (Blueprint $table) {
                $table->dropColumn('content');
            });
        }
    }
};
