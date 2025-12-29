<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id');
            $table->string('name');
            $table->string('subject')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->foreign('teacher_id')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};

