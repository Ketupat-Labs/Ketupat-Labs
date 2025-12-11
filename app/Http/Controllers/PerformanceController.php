<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use App\Models\QuizAttempt;
use App\Models\Submission;
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

        // Get lessons assigned to this classroom through lesson_assignments pivot table
        $lessons = $selectedClass->lessons;

        // Get Students
        $students = User::whereHas('enrolledClassrooms', function ($query) use ($selectedClassId) {
            $query->where('classes.id', $selectedClassId);
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
                    // Use QuizAttempt which uses user_id (matches our User model)
                    $quizAttempt = QuizAttempt::where('user_id', $student->id)
                        ->where('lesson_id', $lesson->id)
                        ->where('submitted', true)
                        ->first();

                    $score = $quizAttempt ? $quizAttempt->score : 0;
                    $max = $quizAttempt ? $quizAttempt->total_questions : 0;

                    $studentRow['grades'][$lesson->id] = [
                        'score' => $score,
                        'max' => $max,
                        'display' => $quizAttempt ? "$score/$max" : '-'
                    ];

                    if ($quizAttempt) {
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
                    // Use QuizAttempt which uses user_id (matches our User model)
                    $quizAttempt = QuizAttempt::where('user_id', $student->id)
                        ->where('lesson_id', $selectedLesson->id)
                        ->where('submitted', true)
                        ->first();

                    // Check for teacher grade from Submission
                    $submission = Submission::where('user_id', $student->id)
                        ->where('lesson_id', $selectedLesson->id)
                        ->first();
                    $teacherGrade = $submission && $submission->grade !== null ? $submission->grade : '-';

                    // Extract answers from quizAttempt if available
                    $answers = $quizAttempt && $quizAttempt->answers ? json_decode($quizAttempt->answers, true) : [];
                    $s1 = isset($answers['q0']) && $answers['q0'] ? '✓' : ($quizAttempt ? '✗' : '-');
                    $s2 = isset($answers['q1']) && $answers['q1'] ? '✓' : ($quizAttempt ? '✗' : '-');
                    $s3 = isset($answers['q2']) && $answers['q2'] ? '✓' : ($quizAttempt ? '✗' : '-');

                    $data[] = [
                        'student' => $student,
                        's1' => $s1,
                        's2' => $s2,
                        's3' => $s3,
                        'teacher_grade' => $teacherGrade,
                        'total_marks' => $quizAttempt ? $quizAttempt->score : 0,
                        'max' => $quizAttempt ? $quizAttempt->total_questions : 0
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
