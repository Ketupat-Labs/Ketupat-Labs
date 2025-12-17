<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('points_earned')->default(0);
            $table->json('answers')->nullable();
            $table->boolean('submitted')->default(false);
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};

