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
        if (Schema::hasTable('class_student')) {
            // First, delete orphaned records that reference non-existent classrooms
            $validClassIds = DB::table('class')->pluck('id')->toArray();
            if (!empty($validClassIds)) {
                DB::table('class_student')
                    ->whereNotIn('classroom_id', $validClassIds)
                    ->delete();
            } else {
                // If no valid classes exist, delete all records
                DB::table('class_student')->truncate();
            }
            
            // Drop existing foreign key constraints using raw SQL to handle different constraint names
            $constraints = [
                'class_students_classroom_id_foreign',
                'class_student_classroom_id_foreign',
            ];
            
            foreach ($constraints as $constraintName) {
                try {
                    DB::statement("ALTER TABLE `class_student` DROP FOREIGN KEY `{$constraintName}`");
                    break; // Successfully dropped, exit loop
                } catch (\Exception $e) {
                    // Continue to next constraint name
                }
            }
            
            $studentConstraints = [
                'class_students_student_id_foreign',
                'class_student_student_id_foreign',
            ];
            
            foreach ($studentConstraints as $constraintName) {
                try {
                    DB::statement("ALTER TABLE `class_student` DROP FOREIGN KEY `{$constraintName}`");
                    break; // Successfully dropped, exit loop
                } catch (\Exception $e) {
                    // Continue to next constraint name
                }
            }
            
            // Recreate foreign keys with correct table names
            Schema::table('class_student', function (Blueprint $table) {
                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('class')
                    ->onDelete('cascade');
                    
                $table->foreign('student_id')
                    ->references('id')
                    ->on('user')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('class_student')) {
            Schema::table('class_student', function (Blueprint $table) {
                // Drop the corrected foreign keys
                try {
                    $table->dropForeign(['classroom_id']);
                } catch (\Exception $e) {
                    // Continue
                }
                
                try {
                    $table->dropForeign(['student_id']);
                } catch (\Exception $e) {
                    // Continue
                }
            });
            
            // Recreate with old table names (if needed for rollback)
            Schema::table('class_student', function (Blueprint $table) {
                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('classes')
                    ->onDelete('cascade');
                    
                $table->foreign('student_id')
                    ->references('id')
                    ->on('user')
                    ->onDelete('cascade');
            });
        }
    }
};
