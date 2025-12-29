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
        Schema::create('ai_generated_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('class')->onDelete('set null');
            $table->json('source_document_ids')->nullable();
            $table->enum('content_type', ['summary_notes', 'quiz'])->default('summary_notes');
            $table->string('question_type')->nullable(); // mcq, structured, mixed
            $table->json('content')->nullable();
            $table->string('title');
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->text('error_message')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_content');
    }
};
