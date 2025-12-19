<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('enrollment')) {
            Schema::create('enrollment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lesson')->onDelete('cascade');
            $table->enum('status', ['enrolled', 'in_progress', 'completed'])->default('enrolled');
            $table->integer('progress')->default(0);
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment');
    }
};

