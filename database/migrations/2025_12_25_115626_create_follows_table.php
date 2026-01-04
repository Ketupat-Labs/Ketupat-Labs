<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('follow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('user')->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only follow another user once
            $table->unique(['follower_id', 'following_id']);
            
            // Indexes for faster queries
            $table->index('follower_id');
            $table->index('following_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow');
    }
};
