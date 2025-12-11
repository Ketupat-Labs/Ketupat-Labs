<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('forum')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('user')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('category')->nullable();
            $table->enum('post_type', ['discussion', 'question', 'announcement'])->default('discussion');
            $table->boolean('is_pinned')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_post');
    }
};

