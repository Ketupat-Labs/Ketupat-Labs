<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use App\Models\QuizAttempt;
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
                'classrooms' => $classrooms,
                'selectedClass' => null,
                'progressData' => [],
                'lessons' => [],
                'summary' => ['totalStudents' => 0, 'totalLessons' => 0, 'lessonCompletion' => []]
            ]);
        }

        // Get students enrolled in the selected classroom
        $students = \App\Models\User::whereHas('enrolledClassrooms', function ($query) use ($selectedClassId) {
            $query->where('classes.id', $selectedClassId);
        })->get();

        // Get lessons assigned to this classroom through lesson_assignments pivot table
        $lessons = $selectedClass->lessons;

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

            $totalProgressSum = 0;

            foreach ($lessons as $lesson) {
                // Use Enrollment for progress tracking to align with lesson view
                $enrollment = \App\Models\Enrollment::where('user_id', $student->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();

                $progressValue = $enrollment ? $enrollment->progress : 0;
                $totalProgressSum += $progressValue;

                if ($progressValue == 100) {
                    $status = 'Completed';
                } elseif ($progressValue > 0) {
                    $status = 'In Progress';
                } else {
                    $status = 'Not Started';
                }

                $studentProgress['completedCount'] += ($progressValue == 100 ? 1 : 0);

                $studentProgress['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'progress' => $progressValue // Pass the actual percentage
                ];
            }

            // Calculate completion percentage (Average of all lesson progresses)
            if ($studentProgress['totalLessons'] > 0) {
                $studentProgress['completionPercentage'] = round(
                    $totalProgressSum / $studentProgress['totalLessons'],
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
            // Count enrollments that are completed (progress = 100)
            // Assuming strict completion. Alternatively, could average the progress of all students?
            // User requested "peratus penyelesaian" alignment.
            // For the summary table, "Completed" usually means 100%.

            $completedCount = \App\Models\Enrollment::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $students->pluck('id'))
                ->where('progress', 100)
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


