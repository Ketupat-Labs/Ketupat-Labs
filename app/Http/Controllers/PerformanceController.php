<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = Classroom::all();
        $selectedClassId = $request->get('class_id', $classrooms->first()->id ?? null);
        $selectedLessonId = $request->get('lesson_id', 'all');

        $selectedClass = $classrooms->find($selectedClassId);

        // If no class found, return empty view
        if (!$selectedClass) {
            return view('performance.index', [
                'classrooms' => $classrooms,
                'lessons' => [],
                'selectedClass' => null,
                'selectedLessonId' => $selectedLessonId,
                'data' => [],
                'mode' => 'none'
            ]);
        }

        // Get Lessons (Assuming lessons match class name or just all lessons for now)
        // Ideally we use Lesson assignments, but following previous pattern:
        $lessons = Lesson::where('class', $selectedClass->name)->get();
        if ($lessons->isEmpty()) {
            $lessons = Lesson::where('teacher_id', $selectedClass->teacher_id)->get();
        }

        // Get Students
        $students = User::whereHas('enrolledClassrooms', function ($query) use ($selectedClassId) {
            $query->where('classrooms.id', $selectedClassId);
        })->get();

        $data = [];
        $mode = ($selectedLessonId === 'all') ? 'all' : 'lesson';

        if ($mode === 'all') {
            // View Mode A: All Lessons Summary
            foreach ($students as $student) {
                $studentRow = [
                    'student' => $student,
                    'grades' => [],
                    'total_score' => 0,
                    'max_score' => 0,
                    'average' => 0
                ];

                foreach ($lessons as $lesson) {
                    $answer = StudentAnswer::where('user_id', $student->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();

                    $score = $answer ? $answer->total_marks : 0;
                    $max = 3; // Assuming 3 questions per lesson

                    $studentRow['grades'][$lesson->id] = [
                        'score' => $score,
                        'max' => $max,
                        'display' => $answer ? "$score/$max" : '-'
                    ];

                    if ($answer) {
                        $studentRow['total_score'] += $score;
                        $studentRow['max_score'] += $max;
                    }
                }

                if ($studentRow['max_score'] > 0) {
                    $studentRow['average'] = round(($studentRow['total_score'] / $studentRow['max_score']) * 100, 1); // 4.0 scale or percentage? Screenshot shows 2.3, 3.0 etc. implies GPA style or just raw avg out of 3? 
                    // Screenshot shows "2.3", "3", "2", "1.5". This looks like average marks (out of 3).
                    // Calculation: Total Marks / Count of Attempted Lessons that have marks? 
                    // Or Total Marks / Total Lessons?
                    // Let's go with Average Mark per Lesson (0-3 scale).
                    $attemptedCount = count(array_filter($studentRow['grades'], fn($g) => $g['display'] !== '-'));
                    if ($attemptedCount > 0) {
                        $studentRow['average'] = round($studentRow['total_score'] / $attemptedCount, 1);
                    }
                }

                $data[] = $studentRow;
            }
        } else {
            // View Mode B: Specific Lesson Breakdown
            $selectedLesson = $lessons->find($selectedLessonId);

            // If lesson doesn't exist in filtered list, fallback to all? Or empty?
            if ($selectedLesson) {
                foreach ($students as $student) {
                    $answer = StudentAnswer::where('user_id', $student->id)
                        ->where('lesson_id', $selectedLesson->id)
                        ->first();

                    $data[] = [
                        'student' => $student,
                        's1' => $answer ? ($answer->q1_answer ? '✓' : '✗') : '-',
                        's2' => $answer ? ($answer->q2_answer ? '✓' : '✗') : '-',
                        's3' => $answer ? ($answer->q3_answer ? '✓' : '✗') : '-', // Using boolean logic from DB
                        'total_marks' => $answer ? $answer->total_marks : 0,
                        'max' => 3
                    ];
                }
            } else {
                $mode = 'none'; // Lesson invalid
            }
        }

        return view('performance.index', compact(
            'classrooms',
            'lessons',
            'selectedClass',
            'selectedLessonId',
            'data',
            'mode'
        ));
    }
}
