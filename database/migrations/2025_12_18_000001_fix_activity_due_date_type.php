<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_assignment', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_assignment', function (Blueprint $table) {
            $table->string('due_date')->nullable()->change();
        });
    }
};
