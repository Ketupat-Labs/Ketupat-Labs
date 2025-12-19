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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category_slug');
            $table->string('icon');
            $table->string('requirement_type'); // e.g., 'principles_learned', 'prototypes_created'
            $table->integer('requirement_value');
            $table->string('color')->nullable(); // e.g., 'primary', 'success'
            $table->timestamps();
            
            // Optional: Add indexes for better performance
            $table->index('category_slug');
            $table->index('requirement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};