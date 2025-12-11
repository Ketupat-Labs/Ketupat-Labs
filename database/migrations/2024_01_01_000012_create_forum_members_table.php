<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('forum')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->enum('role', ['member', 'moderator', 'admin'])->default('member');
            $table->timestamps();

            // Ensure one membership per user per forum
            $table->unique(['forum_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_member');
    }
};

