<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        // Get all classrooms
        $classrooms = \App\Models\Classroom::all();

        // Determine selected classroom (default to first one if exists)
        $selectedClassId = $request->get('class_id', $classrooms->first()->id ?? null);
        $selectedClass = $classrooms->find($selectedClassId);

        if (!$selectedClass) {
            return view('progress.index', [
                'classes' => $classrooms,
                'selectedClass' => null,
                'progressData' => [],
                'lessons' => [],
                'summary' => ['totalStudents' => 0, 'totalLessons' => 0, 'lessonCompletion' => []]
            ]);
        }

        // Get students enrolled in the selected classroom
        $students = \App\Models\User::whereHas('enrolledClassrooms', function ($query) use ($selectedClassId) {
            $query->where('classrooms.id', $selectedClassId);
        })->get();

        // Get lessons assigned to this classroom (or filtering by the string 'class' column if that's the legacy design, 
        // but ideally we check LessonAssignments or similar. For now, we'll try to match the class name if lessons.class exists, 
        // OR just show all lessons, OR show lessons created by the teacher of the classroom? 
        // Let's assume lessons have a 'class' string that matches the classroom name, as per the tracking module design).
        $lessons = Lesson::where('class', $selectedClass->name)->get();

        // Fallback: If no lessons found by name, maybe show all lessons (dev choice) or empty.
        // Let's try to query by teacher_id if available or just show all for now if count is 0.
        if ($lessons->isEmpty()) {
            $lessons = Lesson::where('teacher_id', $selectedClass->teacher_id)->get();
        }

        // Build progress data
        $progressData = [];

        foreach ($students as $student) {
            $studentProgress = [
                'student' => $student,
                'lessons' => [],
                'completedCount' => 0,
                'totalLessons' => $lessons->count(),
                'completionPercentage' => 0
            ];

            foreach ($lessons as $lesson) {
                // Use user_id instead of student_id
                $answer = StudentAnswer::where('user_id', $student->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();

                if ($answer) {
                    $percentage = ($answer->total_marks / 3) * 100;
                    $status = 'Completed';
                    if ($percentage <= 20) {
                        $status = 'Completed (Low Score)';
                    }
                    $studentProgress['completedCount']++;
                } else {
                    $status = 'Not Started';
                }

                $studentProgress['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'answer' => $answer,
                    'percentage' => $answer ? ($answer->total_marks / 3) * 100 : 0
                ];
            }

            // Calculate completion percentage
            if ($studentProgress['totalLessons'] > 0) {
                $studentProgress['completionPercentage'] = round(
                    ($studentProgress['completedCount'] / $studentProgress['totalLessons']) * 100,
                    1
                );
            }

            $progressData[] = $studentProgress;
        }

        // Calculate summary statistics
        $summary = [
            'totalStudents' => $students->count(),
            'totalLessons' => $lessons->count(),
            'lessonCompletion' => []
        ];

        foreach ($lessons as $lesson) {
            $completedCount = StudentAnswer::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $students->pluck('id'))
                ->count();

            $summary['lessonCompletion'][$lesson->id] = [
                'lesson' => $lesson,
                'completed' => $completedCount,
                'total' => $students->count(),
                'percentage' => $students->count() > 0 ? round(($completedCount / $students->count()) * 100, 1) : 0
            ];
        }

        return view('progress.index', compact(
            'classrooms',
            'selectedClass',
            'progressData',
            'lessons',
            'summary'
        ));
    }
}


