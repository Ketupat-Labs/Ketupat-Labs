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
        if (!Schema::hasTable('user_badges')) {
            Schema::create('user_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
                $table->string('badge_code');
                $table->string('status')->default('locked'); // earned, locked, redeemed
                $table->timestamp('earned_at')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('badge_code');
                
                // Optional: Foreign key if you want to enforce referential integrity
                // $table->foreign('badge_code')->references('code')->on('badges')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
