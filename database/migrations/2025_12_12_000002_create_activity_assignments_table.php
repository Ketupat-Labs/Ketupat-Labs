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
        if (!Schema::hasTable('activity_assignment')) {
            Schema::create('activity_assignment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activity')->onDelete('cascade');
                $table->foreignId('classroom_id')->constrained('class')->onDelete('cascade');
                $table->timestamp('assigned_at')->useCurrent();
                $table->string('status')->default('assigned');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_assignment');
    }
};
