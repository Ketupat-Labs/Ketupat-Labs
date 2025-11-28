<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('class_students')) {
            Schema::create('class_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id');
                $table->foreignId('student_id');
                $table->timestamp('enrolled_at')->useCurrent();

                $table->unique(['classroom_id', 'student_id']);
                $table->index('classroom_id');
                $table->index('student_id');

                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnDelete();

                $table->foreign('student_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_students');
    }
};
