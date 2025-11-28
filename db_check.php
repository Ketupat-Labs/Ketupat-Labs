<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking and Creating Tables...\n";

    // 1. Classrooms (New Schema)
    if (!\Illuminate\Support\Facades\Schema::hasTable('classrooms')) {
        echo "Creating classrooms table...\n";
        \Illuminate\Support\Facades\Schema::create('classrooms', function ($table) {
            $table->id();
            $table->foreignId('teacher_id');
            $table->string('name');
            $table->string('subject')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
        echo "Classrooms table created.\n";
    }

    // 2. Class Students
    if (!\Illuminate\Support\Facades\Schema::hasTable('class_students')) {
        echo "Creating class_students table...\n";
        \Illuminate\Support\Facades\Schema::create('class_students', function ($table) {
            $table->id();
            $table->foreignId('classroom_id');
            $table->foreignId('student_id');
            $table->timestamp('enrolled_at')->useCurrent();

            $table->unique(['classroom_id', 'student_id']);
            $table->index('classroom_id');
            $table->index('student_id');

            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
        echo "Class Students table created.\n";
    }

    // 3. Lesson Assignments
    if (!\Illuminate\Support\Facades\Schema::hasTable('lesson_assignments')) {
        echo "Creating lesson_assignments table...\n";
        \Illuminate\Support\Facades\Schema::create('lesson_assignments', function ($table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['Mandatory', 'Optional'])->default('Mandatory');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
        echo "Lesson assignments table created.\n";
    }

    // 4. Enrollments
    if (!\Illuminate\Support\Facades\Schema::hasTable('enrollments')) {
        echo "Creating enrollments table...\n";
        \Illuminate\Support\Facades\Schema::create('enrollments', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['enrolled', 'in_progress', 'completed'])->default('enrolled');
            $table->integer('progress')->default(0);
            $table->timestamps();
        });
        echo "Enrollments table created.\n";
    }

    // 5. Lessons (Update to include classroom_id)
    if (!\Illuminate\Support\Facades\Schema::hasColumn('lessons', 'classroom_id')) {
        echo "Adding classroom_id to lessons table...\n";
        \Illuminate\Support\Facades\Schema::table('lessons', function ($table) {
            $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('set null');
        });
        echo "classroom_id added to lessons table.\n";
    }

    // Fake Migrations to keep Laravel happy
    $migrations = [
        '2025_11_25_143510_create_classrooms_table',
        '2025_11_25_143511_create_lesson_assignments_table',
        '2025_11_25_150551_create_enrollments_table',
        '2025_11_26_000000_create_class_students_table'
    ];

    foreach ($migrations as $migration) {
        $exists = \Illuminate\Support\Facades\DB::table('migrations')->where('migration', $migration)->exists();
        if (!$exists) {
            \Illuminate\Support\Facades\DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => 99
            ]);
            echo "Fake migration record inserted for $migration.\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
