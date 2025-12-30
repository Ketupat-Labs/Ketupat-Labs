<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversation')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            // Ensure one participation per user per conversation
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participant');
    }
};

