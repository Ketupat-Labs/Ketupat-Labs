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
        // Ensure strictly creating the activity_submission table if it doesn't exist
        if (!Schema::hasTable('activity_submission')) {
            Schema::create('activity_submission', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_assignment_id')->constrained('activity_assignment')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
                $table->integer('score')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_submission');
    }
};
