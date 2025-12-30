<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->string('target_type'); // 'post' or 'comment'
            $table->unsignedBigInteger('target_id');
            $table->string('reaction_type'); // 'like', 'love', 'laugh', etc.
            $table->timestamps();

            // Ensure one reaction per user per target
            $table->unique(['user_id', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reaction');
    }
};

