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
        Schema::create('report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('user')->onDelete('cascade');
            $table->string('reportable_type'); // 'post' or 'comment'
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason'); // 'spam', 'harassment', 'inappropriate', 'misinformation', 'other'
            $table->text('details')->nullable(); // Additional details
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('user')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            // Prevent duplicate reports from same user
            $table->unique(['reporter_id', 'reportable_type', 'reportable_id']);
            // Index for faster queries
            $table->index(['reportable_type', 'reportable_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
