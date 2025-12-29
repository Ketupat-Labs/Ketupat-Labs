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
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('name_bm')->nullable(); // Added name_bm
                $table->text('description');
                $table->string('category_code'); // Renamed from category_slug
                $table->string('icon');
                $table->string('requirement_type');
                $table->integer('points_required'); // Renamed from requirement_value
                $table->string('color')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->integer('xp_reward')->default(0);
                $table->timestamps();
                
                $table->foreign('category_id')->references('id')->on('badge_categories')->onDelete('set null');
                $table->index('category_code');
                $table->index('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
