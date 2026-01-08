<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_bulk_assign_lesson_with_dates()
    {
        // 1. Setup Data
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::create([
            'name' => 'Math 101',
            'subject' => 'Math',
            'teacher_id' => $teacher->id,
            'code' => 'MATH101' // Assuming factory or fillable needs code
        ]);

        // Enroll students in classroom (assuming relationship exists or manual pivots)
        // Check Classroom model for students relation logic, usually pivot or user table.
        // Assuming direct pivot for simplicity or using attach if relationship defined.
        $classroom->students()->attach([$student1->id, $student2->id]);

        $lesson = Lesson::create([
            'title' => 'Algebra Basics',
            'topic' => 'Algebra',
            'teacher_id' => $teacher->id,
            'is_published' => true,
            'content_blocks' => []
        ]);

        // 2. Mock Session/Auth
        $this->actingAs($teacher)
            ->withSession(['user_id' => $teacher->id]);

        // 3. Submit Assignment Form
        $assignedAt = now()->format('Y-m-d H:i:00');
        $dueDate = now()->addDays(7)->format('Y-m-d H:i:00');

        $response = $this->post(route('assignments.store'), [
            'classroom_ids' => [$classroom->id],
            'lessons' => [$lesson->id],
            'assigned_at' => $assignedAt,
            'due_date' => $dueDate,
            'notes' => 'Complete by next week.',
            'is_public' => 0
        ]);

        // 4. Assertions
        $response->assertRedirect();

        // Assert Assignment Created
        $this->assertDatabaseHas('lesson_assignment', [
            'classroom_id' => $classroom->id,
            'lesson_id' => $lesson->id,
            'assigned_at' => $assignedAt,
            'due_date' => $dueDate,
            'notes' => 'Complete by next week.'
        ]);

        // Assert Students Enrolled
        $this->assertDatabaseHas('enrollment', [
            'user_id' => $student1->id,
            'lesson_id' => $lesson->id
        ]);
        $this->assertDatabaseHas('enrollment', [
            'user_id' => $student2->id,
            'lesson_id' => $lesson->id
        ]);
    }

    public function test_teacher_cannot_assign_invalid_dates()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $classroom = Classroom::create([
            'name' => 'Science 101',
            'subject' => 'Science',
            'teacher_id' => $teacher->id,
            'code' => 'SCI101'
        ]);
        $lesson = Lesson::create([
            'title' => 'Biology',
            'topic' => 'Bio',
            'teacher_id' => $teacher->id,
            'content_blocks' => []
        ]);

        $this->actingAs($teacher)
            ->withSession(['user_id' => $teacher->id]);

        // POST Due Date BEFORE Start Date
        $assignedAt = now()->format('Y-m-d H:i:00');
        $dueDate = now()->subDay()->format('Y-m-d H:i:00');

        $response = $this->post(route('assignments.store'), [
            'classroom_ids' => [$classroom->id],
            'lessons' => [$lesson->id],
            'assigned_at' => $assignedAt,
            'due_date' => $dueDate, // Invalid
        ]);

        $response->assertSessionHasErrors(['due_date']);

        $this->assertDatabaseMissing('lesson_assignment', [
            'lesson_id' => $lesson->id
        ]);
    }
}
