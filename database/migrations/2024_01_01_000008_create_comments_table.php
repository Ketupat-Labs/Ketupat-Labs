<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('forum_post')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comment')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->integer('reaction_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment');
    }
};
