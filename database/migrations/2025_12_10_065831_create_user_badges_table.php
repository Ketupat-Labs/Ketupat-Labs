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
                $table->string('badge_name');
                $table->string('badge_type')->nullable(); // e.g., 'achievement', 'milestone', 'special'
                $table->text('description')->nullable();
                $table->string('icon_url')->nullable();
                $table->timestamp('earned_at')->useCurrent();
                $table->timestamps();
                
                $table->index('user_id');
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
