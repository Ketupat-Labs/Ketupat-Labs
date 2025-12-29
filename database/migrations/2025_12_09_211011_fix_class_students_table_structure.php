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
        if (Schema::hasTable('class_students')) {
            // Check if classroom_id column exists
            if (!Schema::hasColumn('class_students', 'classroom_id')) {
                // Add the missing classroom_id column
                Schema::table('class_students', function (Blueprint $table) {
                    $table->foreignId('classroom_id')->after('id')->constrained()->onDelete('cascade');
                });
            }
            
            // Check if student_id column exists
            if (!Schema::hasColumn('class_students', 'student_id')) {
                // Add the missing student_id column
                Schema::table('class_students', function (Blueprint $table) {
                    $table->foreignId('student_id')->after('classroom_id')->constrained('user')->onDelete('cascade');
                });
            }
            
            // Check if enrolled_at column exists
            if (!Schema::hasColumn('class_students', 'enrolled_at')) {
                Schema::table('class_students', function (Blueprint $table) {
                    $table->timestamp('enrolled_at')->nullable()->after('student_id');
                });
            }
        } else {
            // Table doesn't exist, create it
            Schema::create('class_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->constrained('user')->onDelete('cascade');
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table, just remove columns if needed
        if (Schema::hasTable('class_students')) {
            Schema::table('class_students', function (Blueprint $table) {
                if (Schema::hasColumn('class_students', 'classroom_id')) {
                    $table->dropForeign(['classroom_id']);
                    $table->dropColumn('classroom_id');
                }
                if (Schema::hasColumn('class_students', 'student_id')) {
                    $table->dropForeign(['student_id']);
                    $table->dropColumn('student_id');
                }
                if (Schema::hasColumn('class_students', 'enrolled_at')) {
                    $table->dropColumn('enrolled_at');
                }
            });
        }
    }
};
