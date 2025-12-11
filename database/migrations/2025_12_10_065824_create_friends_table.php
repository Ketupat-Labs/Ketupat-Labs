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
        if (!Schema::hasTable('friends')) {
            Schema::create('friends', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
                $table->foreignId('friend_id')->constrained('user')->onDelete('cascade');
                $table->enum('status', ['pending', 'accepted', 'blocked'])->default('pending');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                
                // Ensure unique friendship pairs
                $table->unique(['user_id', 'friend_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
