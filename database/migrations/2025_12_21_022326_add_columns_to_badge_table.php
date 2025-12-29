<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge', function (Blueprint $table) {
            if (!Schema::hasColumn('badge', 'name_bm')) {
                $table->string('name_bm')->nullable();
            }
            if (!Schema::hasColumn('badge', 'type')) {
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('badge', 'value')) {
                $table->integer('value')->nullable();
            }
            if (!Schema::hasColumn('badge', 'extra')) {
                $table->integer('extra')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('badge', function (Blueprint $table) {
            $table->dropColumn(['name_bm', 'type', 'value', 'extra']);
        });
    }
};
