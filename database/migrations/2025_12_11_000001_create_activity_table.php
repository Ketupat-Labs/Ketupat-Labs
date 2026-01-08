<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('activity')) {
            Schema::create('activity', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('user')->onDelete('cascade');
                $table->string('title');
                $table->string('type'); // 'Game', 'Quiz'
                $table->string('suggested_duration')->nullable();
                $table->text('description')->nullable();
                $table->json('content')->nullable();

                // Adding is_public here to ensure base table has it, 
                // in case the add_is_public migration is skipped or strictly checks "after" columns that might differ.
                // The later migration checks Schema::hasColumn so it won't crash.
                $table->boolean('is_public')->default(false);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity');
    }
};
