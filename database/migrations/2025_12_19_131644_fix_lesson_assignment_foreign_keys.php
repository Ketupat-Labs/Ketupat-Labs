<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('lesson_assignment')) {
            Schema::table('lesson_assignment', function (Blueprint $table) {
                // Drop existing foreign key constraints
                try {
                    $table->dropForeign(['classroom_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist or have different name
                    try {
                        DB::statement('ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `lesson_assignments_classroom_id_foreign`');
                    } catch (\Exception $e2) {
                        // Try alternative constraint name
                        try {
                            DB::statement('ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `lesson_assignment_classroom_id_foreign`');
                        } catch (\Exception $e3) {
                            // Constraint doesn't exist, continue
                        }
                    }
                }
                
                try {
                    $table->dropForeign(['lesson_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist or have different name
                    try {
                        DB::statement('ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `lesson_assignments_lesson_id_foreign`');
                    } catch (\Exception $e2) {
                        // Try alternative constraint name
                        try {
                            DB::statement('ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `lesson_assignment_lesson_id_foreign`');
                        } catch (\Exception $e3) {
                            // Constraint doesn't exist, continue
                        }
                    }
                }
            });
            
            // Recreate foreign keys with correct table names
            Schema::table('lesson_assignment', function (Blueprint $table) {
                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('class')
                    ->onDelete('cascade');
                    
                $table->foreign('lesson_id')
                    ->references('id')
                    ->on('lesson')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lesson_assignment')) {
            // Drop the corrected foreign keys using raw SQL
            $constraints = [
                'lesson_assignment_classroom_id_foreign',
                'lesson_assignments_classroom_id_foreign',
            ];
            
            foreach ($constraints as $constraintName) {
                try {
                    DB::statement("ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `{$constraintName}`");
                    break;
                } catch (\Exception $e) {
                    // Continue
                }
            }
            
            $lessonConstraints = [
                'lesson_assignment_lesson_id_foreign',
                'lesson_assignments_lesson_id_foreign',
            ];
            
            foreach ($lessonConstraints as $constraintName) {
                try {
                    DB::statement("ALTER TABLE `lesson_assignment` DROP FOREIGN KEY `{$constraintName}`");
                    break;
                } catch (\Exception $e) {
                    // Continue
                }
            }
            
            // Recreate with old table names (if needed for rollback)
            // Note: This will only work if classes and lessons tables still exist
            if (Schema::hasTable('classes') && Schema::hasTable('lessons')) {
                Schema::table('lesson_assignment', function (Blueprint $table) {
                    $table->foreign('classroom_id')
                        ->references('id')
                        ->on('classes')
                        ->onDelete('cascade');
                        
                    $table->foreign('lesson_id')
                        ->references('id')
                        ->on('lessons')
                        ->onDelete('cascade');
                });
            }
        }
    }
};
