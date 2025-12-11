<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('forum')->onDelete('cascade');
            $table->string('tag_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_tags');
    }
};

