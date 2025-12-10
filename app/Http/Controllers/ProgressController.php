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

            foreach ($lessons as $lesson) {
                // Use QuizAttempt which uses user_id (matches our User model)
                $quizAttempt = QuizAttempt::where('user_id', $student->id)
                    ->where('lesson_id', $lesson->id)
                    ->where('submitted', true)
                    ->first();

                if ($quizAttempt) {
                    $percentage = $quizAttempt->total_questions > 0 
                        ? ($quizAttempt->score / $quizAttempt->total_questions) * 100 
                        : 0;
                    $status = 'Completed';
                    if ($percentage <= 20) {
                        $status = 'Completed (Low Score)';
                    }
                    $studentProgress['completedCount']++;
                } else {
                    $status = 'Not Started';
                    $percentage = 0;
                }

                $studentProgress['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'quizAttempt' => $quizAttempt,
                    'percentage' => $percentage
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
            // Use QuizAttempt which uses user_id (matches our User model)
            $completedCount = QuizAttempt::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $students->pluck('id'))
                ->where('submitted', true)
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


