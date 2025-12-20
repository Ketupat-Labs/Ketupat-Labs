<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('class_student')) {
            Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('class')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('user')->onDelete('cascade');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};

