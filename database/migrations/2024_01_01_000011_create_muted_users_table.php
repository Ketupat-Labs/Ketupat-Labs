<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muted_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('forum')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('muted_by')->constrained('user')->onDelete('cascade');
            $table->timestamp('muted_until')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muted_user');
    }
};

