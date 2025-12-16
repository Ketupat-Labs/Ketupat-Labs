<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
                $table->text('description');
                $table->string('category_slug')->nullable(); // Kept for legacy compatibility if needed
                $table->string('icon')->nullable();
                $table->string('requirement_type')->nullable(); // Nullable
                $table->integer('requirement_value')->nullable(); // Nullable
                $table->string('color')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->integer('xp_reward')->default(0);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('badge_categories')->onDelete('set null');
                // The following indexes were removed as per the provided Code Edit.
                // $table->index('category_slug');
                // $table->index('code');
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
