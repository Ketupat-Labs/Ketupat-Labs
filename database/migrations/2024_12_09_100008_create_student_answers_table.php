<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_answer')) {
            Schema::create('student_answer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreignId('lesson_id')->constrained('lesson')->onDelete('cascade');
            $table->string('q1_answer')->nullable();
            $table->string('q2_answer')->nullable();
            $table->string('q3_answer')->nullable();
            $table->integer('total_marks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answer');
    }
};

