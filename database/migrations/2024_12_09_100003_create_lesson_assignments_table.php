<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('lesson_assignments')) {
            Schema::create('lesson_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
                $table->string('type')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->date('due_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assignments');
    }
};

