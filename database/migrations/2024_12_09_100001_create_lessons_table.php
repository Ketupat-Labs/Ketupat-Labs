<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lessons')) {
            Schema::create('lessons', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('topic');
                $table->foreignId('teacher_id')->constrained('user')->onDelete('cascade');
                $table->integer('duration')->nullable();
                $table->string('material_path')->nullable();
                $table->string('url')->nullable();
                $table->text('content')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamps();
            });
        } else {
            // Table exists, check and add missing columns if needed
            Schema::table('lessons', function (Blueprint $table) {
                if (!Schema::hasColumn('lessons', 'url')) {
                    $table->string('url')->nullable()->after('material_path');
                }
                if (!Schema::hasColumn('lessons', 'content')) {
                    $table->text('content')->nullable()->after('topic');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

